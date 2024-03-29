<?php

namespace Drupal\Tests\wxt_library\Kernel;

use Drupal\Tests\SchemaCheckTestTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test migration config entity discovery.
 *
 * @group wxt
 * @group wxt_library
 */
class LibraryTest extends KernelTestBase {

  use SchemaCheckTestTrait;

  /**
   * Installation profile.
   *
   * @var string
   */
  protected $profile = 'wxt';

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'wxt_library',
  ];

  /**
   * Stores the wxt_library plugin manager.
   *
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $pluginManager;

  /**
   * Setup the test.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('wxt_library');
    $this->pluginManager = $this->container->get('wxt_library.service_wxt');
  }

  /**
   * Test the library.
   */
  public function testLibrary() {
    $this->assertSame('wet_boew', $this->pluginManager->getLibraryName());
    $this->assertSame('/libraries/theme-wet-boew', $this->pluginManager->getLibraryPath());
  }

}
