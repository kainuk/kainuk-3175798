<?php namespace Drupal\cmrf_core;

use CMRF\Core\AbstractCall;
use CMRF\Core\Call as CallInterface;

class Call extends AbstractCall {

  protected $request = NULL;

  protected $reply = NULL;

  protected $status = CallInterface::STATUS_INIT;

  protected $metadata = '{}';

  protected $cached_until = NULL;

  public static function createNew($connector_id, $core, $entity, $action, $parameters, $options, $callbacks, $factory) {
    $call = new Call($core, $connector_id, $factory);

    // compile request
    $call->request               = $call->compileRequest($parameters, $options);
    $call->request['entity']     = $entity;
    $call->request['action']     = $action;
    $call->status                = CallInterface::STATUS_INIT;
    $call->metadata              = [];
    $call->metadata['callbacks'] = $callbacks;
    if (is_array($callbacks)) {
      $call->callbacks = $callbacks;
    }

    // Set the retry options
    if (isset($options['retry_count'])) {
      $call->retry_count = $options['retry_count'];
    }
    if (isset($options['retry_interval'])) {
      $call->metadata['retry_interval'] = $options['retry_interval'];
    }
    foreach ($options as $key => $val) {
      $call->metadata[$key] = $val;
    }

    // set the caching flag
    if (!empty($options['cache'])) {
      $call->cached_until = new \DateTime();
      $call->cached_until->modify('+' . $options['cache']);
    }

    return $call;
  }

  public static function createWithRecord($connector_id, $core, $record, $factory) {
    $call              = new Call($core, $connector_id, $factory, $record->cid);
    $call->status      = $record->status;
    $call->metadata    = json_decode($record->metadata, TRUE);
    $call->retry_count = $record->retry_count;
    if (!empty($record->cached_until)) {
      $call->cached_until = new \DateTime($record->cached_until);
    }
    $call->request = json_decode($record->request, TRUE);
    $call->reply   = json_decode($record->reply, TRUE);
    $call->date    = new \DateTime($record->create_date);
    if (!empty($record->reply_date)) {
      $call->reply_date = new \DateTime($record->reply_date);
    }
    if (!empty($record->scheduled_date)) {
      $call->scheduled_date = new \DateTime($record->scheduled_date);
    }
    if (isset($call->metadata['callbacks']) && is_array($call->metadata['callbacks'])) {
      $call->callbacks = $call->metadata['callbacks'];
    }
    return $call;
  }

  public function setReply($data, $newstatus = CallInterface::STATUS_DONE) {
    // update the cached data
    $this->reply      = $data;
    $this->reply_date = new \DateTime();
    $this->status     = $newstatus;
    $this->checkForRetry();
    $this->factory->update($this);
    $this->checkAndTriggerFailure();
    $this->checkAndTriggerDone();
  }

  public function setID($id) {
    parent::setID($id);
  }

  public function getEntity() {
    return $this->request['entity'];
  }

  public function getAction() {
    return $this->request['action'];
  }

  public function getParameters() {
    return $this->extractParameters($this->request);
  }

  public function getOptions() {
    return $this->extractOptions($this->request);
  }

  public function getStatus() {
    return $this->status;
  }

  public function getMetadata() {
    return $this->metadata;
  }

  public function getCachedUntil() {
    return $this->cached_until;
  }

  public function getRequest() {
    return $this->request;
  }

  public function getReply() {
    return $this->reply;
  }

  public function triggerCallback() {
    // TODO:
  }

  public function setStatus($status, $error_message, $error_code = NULL) {
    $error = [
      'is_error'      => '1',
      'error_message' => $error_message,
      'error_code'    => $error_code,
    ];

    $this->status     = $status;
    $this->reply      = $error;
    $this->reply_date = new \DateTime();
    $this->checkForRetry();

    $this->factory->update($this);
    $this->checkAndTriggerFailure();
    $this->checkAndTriggerDone();
  }

  protected function checkForRetry() {
    if ($this->status == \CMRF\Core\Call::STATUS_FAILED && $this->retry_count > 0) {
      $this->retry_count    = $this->retry_count - 1;
      $this->scheduled_date = $this->getRetryScheduledDate();
      $this->status         = \CMRF\Core\Call::STATUS_RETRY;
    }
  }

  protected function getRetryScheduledDate() {
    $default_retry_interval = '10 minutes';
    $now                    = new \DateTime();
    if (isset($this->metadata['retry_interval'])) {
      $now->modify('+ ' . $this->metadata['retry_interval']);
      return $now;
    }

    $now->modify('+ ' . $default_retry_interval);
    return $now;
  }

  protected function checkAndTriggerFailure() {
    if ($this->status == \CMRF\Core\Call::STATUS_FAILED) {
      \Drupal::moduleHandler()->invokeAll('cmrf_core_call_failed', ['call' => $this]);
    }
  }

  protected function checkAndTriggerDone() {
    if ($this->status == \CMRF\Core\Call::STATUS_DONE) {
      \Drupal::moduleHandler()->invokeAll('cmrf_core_call_done', ['call' => $this]);
    }
  }


}
