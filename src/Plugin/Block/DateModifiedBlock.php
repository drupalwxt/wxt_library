<?php

namespace Drupal\wxt_library\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'DateModifiedBlock' block.
 *
 * @Block(
 *  id = "date_modified_block",
 *  admin_label = @Translation("Date modified block"),
 * )
 */
class DateModifiedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The date time to collect tokens.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   Entity storage for node entities.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        DateFormatter $date_formatter,
        RequestStack $request_stack,
        EntityStorageInterface $node_storage,
        TimeInterface $date_time
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
    $this->requestStack = $request_stack;
    $this->nodeStorage = $node_storage;
    $this->dateTime = $date_time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('request_stack'),
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;

    $date_formats = [
      'tiny' => $this->t('Tiny'),
      'short' => $this->t('Short'),
      'medium' => $this->t('Medium'),
      'long' => $this->t('Long'),
    ];
    $form['wxt_library'] = [
      '#type' => 'details',
      '#title' => $this->t('Date Modified Options'),
      '#description' => $this->t('Show the current language in the language toggle depending on selected theme.'),
      '#open' => FALSE,
    ];
    $form['wxt_library']['date_modified'] = [
      '#type' => 'select',
      '#title' => $this->t('Date Modified'),
      '#description' => $this->t('Date Modified block formats.'),
      '#options' => $date_formats,
      '#default_value' => is_null($config['date_modified']) ? '' : $config['date_modified'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $language_conf = $form_state->getValue('wxt_library');
    $this->configuration['date_modified'] = $language_conf['date_modified'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $format = $this->configuration['date_modified'];
    $time = $this->dateTime->getRequestTime();

    // Node context.
    $node = $this->requestStack->getCurrentRequest()->get('node');
    if (is_object($node)) {
      $time = $node->getChangedTime();
    }

    // Formatting of date.
    if ($format == 'tiny') {
      $formatted_date = 'Y-m-d';
    }
    else {
      $formatted_date = DateFormat::load($format)->getPattern();
    }
    $date = $this->dateFormatter->format($time, 'custom', $formatted_date);

    $build = [];
    $build['date_modified_block']['#markup'] = '<div class="datemod mrgn-bttm-lg"><dl id="wb-dtmd">' . "\n";
    $build['date_modified_block']['#markup'] .= '<dt>' . $this->t('Date modified:') . '</dt>' . "\n";
    $build['date_modified_block']['#markup'] .= '<dd><time property="dateModified">' . $date . '</time></dd>';
    $build['date_modified_block']['#markup'] .= '</dl></div>';
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
