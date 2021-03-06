<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\Core\Url;

class OptionSetDeleteForm extends CMRFWebformDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cmrf_webform_option_set.collection');
  }

}
