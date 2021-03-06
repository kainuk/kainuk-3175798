<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 18.07.17
 * Time: 18:32
 */

namespace Drupal\Tests\cmrf_core\Functional;


use Drupal\cmrf_core\Core;
use Drupal\cmrf_core\Entity\CMRFProfile;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\UnitTestCase;

/**
 * Class InstantiationTests
 *
 * @package Drupal\Tests\cmrf_core\Functional
 * @group cmrf_core
 */
class InstantiationTests extends KernelTestBase {

  protected $profile = 'minimal';
  public static $modules = ['cmrf_core'];

  protected function setUp() {
    parent::setUp();
    $this->installConfig(['cmrf_core']);
  }


  public function testInstantiation() {
    $core = new Core();
    $prop=new \ReflectionProperty('\Drupal\cmrf_core\Core','callfactory');
    $prop->setAccessible(true);
    $factory=$prop->getValue($core);
    $this->assertNotEqual($factory,null);
    $prop=new \ReflectionProperty('\CMRF\PersistenceLayer\SQLPersistingCallFactory','table_name');
    $prop->setAccessible(true);
    $table=$prop->getValue($factory);
    $this->assertTrue(stristr($table,"civicrm_api_call") !== false);
  }

  public function testService() {
    $core = \Drupal::service('cmrf_core.core');
    $this->assertTrue($core != NULL);
  }

  public function testProfiles() {
    /** @var Core $core */
    $core = \Drupal::service('cmrf_core.core');
    $profiles = $core->getConnectionProfiles();
    $this->assertTrue(isset($profiles['default']));
  }
}
