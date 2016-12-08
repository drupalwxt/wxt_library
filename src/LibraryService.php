<?php

namespace Drupal\wxt_library;

class LibraryService {

  /**
   * @var string
   */
  protected $libraryName;

  /**
   * @var string
   */
  protected $libraryPath;

  /**
   * When the service is created, set defaults.
   */
  public function __construct() {
    $config = \Drupal::config('wxt_library.settings');
    $wxt_active = $config->get('wxt.theme');
    $this->libraryPath = _wxt_library_get_path($wxt_active, TRUE);

    $wxt_active = str_replace('-', '_', $wxt_active);
    $wxt_active = str_replace('theme_', '', $wxt_active);
    $this->libraryName = $wxt_active;
  }

  /**
   * Return the name of the library.
   */
  public function getLibraryName() {
    return $this->libraryName;
  }

  /**
   * Return the location of the library.
   */
  public function getLibraryPath() {
    return $this->libraryPath;
  }

}
