<?php

namespace Drupal\wxt_library\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Panels Title' condition.
 *
 * @Condition(
 *   id = "panels_title",
 *   label = @Translation("Panels Title"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current Node")),
 *   }
 * )
 */
class PanelsTitle extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
     $configuration,
     $plugin_id,
     $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not display on panelized page(s)'),
      '#default_value' => $this->configuration['enabled'],
      '#description' => $this->t('Disables the display on panelized page(s).'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['enabled'] = $form_state->getValue('enabled');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {

    $node = $this->getContextValue('node');
    if ($node->__isset('panelizer')) {
      $panelizer = $node->get('panelizer');

      $panelizer_values = $node->get('panelizer')->getValue();
      $view_mode = array_pop($panelizer_values);
      $view_mode = $view_mode['view_mode'];

      $panelizer = \Drupal::service('panelizer');

      $displays = $panelizer->getDefaultPanelsDisplays($node->getEntityTypeId(), $node->bundle(), $view_mode);
      $display = $displays['default'];

      $display = $panelizer->getEntityViewDisplay($node->getEntityTypeId(), $node->bundle(), $view_mode);
      $render_display = $display->collectRenderDisplay($node, $view_mode);
      $content = $render_display->get('content');

      if (isset($content['title'])) {
        return FALSE;
      }
    }
    return TRUE;

  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    if (empty($this->configuration['enabled'])) {
      return t('Enabled');
    }
    return t('Disabled');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['enabled' => FALSE] + parent::defaultConfiguration();
  }

}
