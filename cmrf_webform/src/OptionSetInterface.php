<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an OptionSet entity.
 */
interface OptionSetInterface extends ConfigEntityInterface {

  public function getWebformId();

  public function getEntity();
  public function setEntity($value);

  public function getAction();
  public function setAction($value);

  public function getParameters();
  public function getDecodedParameters($as_array);
  public function setParameters($value);

  public function getKeyProperty();
  public function setKeyProperty($value);

  public function getValueProperty();
  public function setValueProperty($value);

  public function getCache();
  public function setCache($value);

  public function needsRecaching();
  public function setRecached();

}
