<?php

namespace Drupal\wxt_library\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\wxt_library\LibraryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the search form for the search block.
 */
class SearchApiBlockForm extends FormBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Drupal\wxt_library\LibraryService definition.
   *
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $wxtLibraryServiceWxT;

  /**
   * Constructs a new SearchBlockForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\wxt_library\LibraryService $wxt_library_service_wxt
   *   The LibraryService.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer,
    LibraryService $wxt_library_service_wxt
  ) {
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->wxtLibraryServiceWxT = $wxt_library_service_wxt;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('renderer'),
      $container->get('wxt_library.service_wxt')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wxt_search_api_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $wxt_active = $this->wxtLibraryServiceWxT->getLibraryName();
    $submit_title = $this->t('Search');

    $form['#action'] = '/search/content';
    $form['#method'] = 'get';

    $form['search_api_fulltext'] = [
      '#id' => 'wb-srch-q',
      '#type' => 'search',
      '#title' => '',
      '#title_display' => 'invisible',
      '#size' => 27,
      '#maxlength' => 128,
      '#default_value' => '',
      '#placeholder' => '',
      '#attributes' => [
        'title' => $this->t('Enter the terms you wish to search for.'),
      ],
    ];

    $form['submit_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['submit'],
      ],
    ];

    $form['submit_container']['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_title,
      '#id' => 'wb-srch-sub',
    ];

    if ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy') {
      $form['submit_container']['submit']['#value'] = '';
      $form['search_api_fulltext']['#placeholder'] = $this->t('Search website');
    }

    $form['#after_build'] = ['::afterBuild'];
    return $form;
  }

  /**
   * Custom after build to remove elements from being submitted as GET variables.
   */
  public function afterBuild(array $element, FormStateInterface $form_state) {
    // Remove the form_build_id, form_id and op from the GET parameters.
    unset($element['form_build_id']);
    unset($element['form_id']);
    unset($element['op']);
    return $element;
   }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Remove unneeded values.
    $form_state->cleanValues();

    parent::submitForm($form, $form_state);
  }

}
