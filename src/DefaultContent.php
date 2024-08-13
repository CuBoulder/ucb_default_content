<?php

namespace Drupal\ucb_default_content;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;
use Drupal\pathauto\PathautoState;

class DefaultContent {

  /**
   * The config factory
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger factory.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs DefaultContent service.
   *
   * @param ConfigFactoryInterface $config_factory
   * @param Connection $database
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Connection $database,
    LoggerChannelFactoryInterface $logger,
  ) {
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->logger = $logger;
  }

  /**
   * Create the default homepage.
   *
   * @return void
   */
  public function create_homepage() {
    $node = Node::create([
      'type' => 'basic_page',
      'title' => 'Home',
      'path' => ['alias' => '/home', 'pathauto' => PathautoState::SKIP],
      'body' => 'Congratulations, your Web Express site is up and running!
  Please click the EDIT button at the top of the screen to edit this page.',
    ]);
    $node->enforceIsNew()->save();
    $nid = $node->id();
    $this->configFactory->getEditable('system.site')->set('page.front', '/node/' . $nid)->save();
    $this->set_simplesitemap_no_index($nid);
  }

  /**
   * Create the default 404 page.
   *
   * @return void
   */
  public function create_404_page() {
    $node = Node::create([
      'type' => 'basic_page',
      'title' => 'Page Not Found',
      'path' => ['alias' => '/404', 'pathauto' => PathautoState::SKIP],
      'body' => 'The page you are looking for appears to have been moved, deleted, or does not exist.',
    ]);
    $node->enforceIsNew()->save();
    $nid = $node->id();
    $this->configFactory->getEditable('system.site')->set('page.404', '/node/' . $nid)->save();
    $this->set_simplesitemap_no_index($nid);
  }

  /**
   * Undocumented function
   *
   * @param string $nid
   *   The id of the node.
   * @return void
   */
  private function set_simplesitemap_no_index($nid) {
    // This method may not be part of the public API but there is no other clean
    // way of doing this. Simple XML Sitemap still uses a custom
    // `simple_sitemap_entity_overrides` DB table to set these overrides. There's
    // an issue to store these overrides as fields on the entity but it's been
    // open since 2019 (!!!):
    // https://www.drupal.org/project/simple_sitemap/issues/3034070
    // BEWARE OF BREAKING CHANGES to Simple XML Sitemap.
    if ($this->database->schema->tableExists('simple_sitemap_entity_overrides')) {
      $this->database->merge('simple_sitemap_entity_overrides')
        ->keys([
          'type' => 'default',
          'entity_type' => 'node',
          'entity_id' => $nid,
        ])
        ->fields([
          'type' => 'default',
          'entity_type' => 'node',
          'entity_id' => $nid,
          'inclusion_settings' => serialize([
            'index' => '0',
            'priority' => '0.5',
            'changefreq' => '',
            'include_images' => '0',
          ]),
        ])
        ->execute();
    }
    else {
      $this->logger->get('ucb_default_content')->warning('Failed to set Simple XML Sitemap no index (table doesn\'t exist). Simple XML Sitemap isn\'t installed or changed something in an update.');
    }
  }
}