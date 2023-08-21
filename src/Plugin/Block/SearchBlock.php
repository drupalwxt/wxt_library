<?php

namespace Drupal\wxt_library\Plugin\Block;

use Drupal\bootstrap\Bootstrap;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wxt_library\LibraryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a WxT 'Search form' block.
 *
 * @Block(
 *   id = "wxt_search_form_block",
 *   admin_label = @Translation("WxT Search"),
 *   category = @Translation("WxT")
 * )
 */
class SearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\wxt_library\LibraryService definition.
   *
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $wxtLibraryServiceWxT;

  /**
   * Constructs an SearchBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\wxt_library\LibraryService $wxt_library_service_wxt
   *   The LibraryService.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ModuleHandlerInterface $module_handler,
    FormBuilderInterface $form_builder,
    LibraryService $wxt_library_service_wxt
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->formBuilder = $form_builder;
    $this->wxtLibraryServiceWxT = $wxt_library_service_wxt;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('form_builder'),
      $container->get('wxt_library.service_wxt')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $wxt_active = $this->wxtLibraryServiceWxT->getLibraryName();
    $wxt_gcweb_search = Bootstrap::getTheme()->getSetting('wxt_gcweb_search');

    if (!empty($wxt_gcweb_search) && ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy' || $wxt_active == 'gc_intranet')) {
      return $this->formBuilder->getForm('Drupal\wxt_library\Form\SearchCanadaBlockForm');
    }
    elseif ($this->moduleHandler->moduleExists('wxt_ext_search')) {
      return $this->formBuilder->getForm('Drupal\wxt_library\Form\SearchApiBlockForm');
    }
    elseif ($this->moduleHandler->moduleExists('search')) {
      return $this->formBuilder->getForm('Drupal\wxt_library\Form\SearchBlockForm');
    }
    else {
      return [];
    }

  }

}
