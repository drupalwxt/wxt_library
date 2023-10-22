<?php

namespace Drupal\wxt_library\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\layout_builder_st\Entity\LayoutBuilderEntityViewDisplay;

/**
 * Provides a 'Layout Builder Title' condition.
 *
 * @Condition(
 *   id = "panels_title",
 *   label = @Translation("Layout Builder Title"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current Node"), required = FALSE),
 *     "taxonomy_term" = @ContextDefinition("entity:taxonomy_term", label = @Translation("Current Taxonomy Term"), required = FALSE),
 *   }
 * )
 */
class PanelsTitle extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a LayoutBuilderTitle condition plugin.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(RequestStack $request_stack, EntityDisplayRepositoryInterface $entity_display_repository, array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_display.repository'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['is_panelized'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Page is panelized with a title:'),
      '#options' => ['enabled' => $this->t('Enabled')],
      '#default_value' => $this->configuration['is_panelized'],
      '#description' => $this->t('Returns TRUE if the page being viewed is a panelized page.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['is_panelized'] = array_filter($form_state->getValue('is_panelized'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if ((empty($this->configuration['is_panelized']) || (isset($this->configuration['is_panelized']['enabled']) && empty($this->configuration['is_panelized']['enabled']))) && !$this->isNegated()) {
      return TRUE;
    }

    // Page Manager support for Panels.
    $request = $this->requestStack->getCurrentRequest();
    $page_manager = $request->attributes->get('page_manager_page');
    if (!empty($page_manager) && $page_manager->access('view')) {
      $variant = $request->attributes->get('page_manager_page_variant');
      if ($variant->access('view')) {
        /** @var \Drupal\ctools\Plugin\BlockVariantInterface $variant_plugin */
        $variant_plugin = $variant->getVariantPlugin();
        if ($variant_plugin->pluginId != 'http_status_code') {
          if (method_exists($variant_plugin, 'getRegionAssignments')) {
            foreach ($variant_plugin->getRegionAssignments() as $blocks) {
              /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
              foreach ($blocks as $block) {
                if ($block->getPluginId() == 'page_title_block') {
                  return TRUE;
                }
              }
            }
          }
        }
      }
    }

    // Layout support for Node / Taxonomy Term.
    $supported_entities = ['node', 'taxonomy_term'];
    foreach ($supported_entities as $supported_entity) {
      $entity = $this->getContextValue($supported_entity);
      if (!empty($entity) && $entity->__isset('layout_builder__layout')) {
        // Layout Builder custom display for individual entity.
        $layout_values = $entity->get('layout_builder__layout')->getValue();
        $section = array_pop($layout_values);
        if (!empty($section['section'])) {
          foreach ($section['section']->getComponents() as $component) {
            $plugin = $component->getPlugin();
            $configuration = $plugin->getConfiguration();
            if ($configuration['id'] == 'field_block:node:page:title' ||
                $configuration['id'] == 'page_title_block') {
              return TRUE;
            }
          }
        }
        else {
          // Layout Builder default display for supported entities.
          $view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity->getEntityTypeId(), $entity->getType());
          foreach (array_keys($view_modes) as $view_mode) {
            $display = wxt_library_entity_get_display($entity->getEntityTypeId(), $entity->getType(), $view_mode);
            if (($display instanceof LayoutBuilderEntityViewDisplay)) {
              if ($display->getComponent('title')) {
                return TRUE;
              }
            }
          }
        }
      }
    }

    // Panelizer support for Node.
    $node = $this->getContextValue('node');
    if (!empty($node) && $node->__isset('panelizer')) {
      $panelizer = $node->get('panelizer');

      $panelizer_values = $node->get('panelizer')->getValue();
      $view_mode = array_pop($panelizer_values);
      $view_mode = $view_mode['view_mode'];

      $panelizer = \Drupal::service('panelizer');

      $displays = $panelizer->getDefaultPanelsDisplays($node->getEntityTypeId(), $node->bundle(), $view_mode);
      if (!array_key_exists('default', $displays)) {
        return TRUE;
      }
      $display = $displays['default'];

      $display = $panelizer->getEntityViewDisplay($node->getEntityTypeId(), $node->bundle(), $view_mode);
      $render_display = $display->collectRenderDisplay($node, $view_mode);
      $content = $render_display->get('content');

      if (isset($content['title'])) {
        return TRUE;
      }
    }

    // Panelizer support for Taxonomy Term.
    $taxonomy_term = $this->getContextValue('taxonomy_term');
    if (!empty($taxonomy_term) && $taxonomy_term->__isset('panelizer')) {

      $panelizer = $taxonomy_term->get('panelizer');

      $panelizer_values = $taxonomy_term->get('panelizer')->getValue();

      $view_mode = array_pop($panelizer_values);
      $view_mode = $view_mode['view_mode'];

      $panelizer = \Drupal::service('panelizer');

      $displays = $panelizer->getDefaultPanelsDisplays($taxonomy_term->getEntityTypeId(), $taxonomy_term->bundle(), $view_mode);
      if (!array_key_exists('default', $displays)) {
        return TRUE;
      }
      $display = $displays['default'];

      $display = $panelizer->getEntityViewDisplay($taxonomy_term->getEntityTypeId(), $taxonomy_term->bundle(), $view_mode);
      $render_display = $display->collectRenderDisplay($taxonomy_term, $view_mode);
      $content = $render_display->get('content');

      if (isset($content['name'])) {
        return TRUE;
      }
    }

    return FALSE;

  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    if (!empty($this->configuration['is_panelized'])) {
      return $this->t('Is a panelized page with a title');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['is_panelized' => []] + parent::defaultConfiguration();
  }

}
