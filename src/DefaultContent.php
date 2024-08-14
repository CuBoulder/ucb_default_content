<?php

namespace Drupal\ucb_default_content;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\PathautoState;

/**
 * Class for creating default content when creating sites.
 */
class DefaultContent {

  /**
   * The config factory.
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
   * @param Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Used to save configuration for created default content.
   * @param Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Used for logging.
   * @param Drupal\Core\Database\Connection $database
   *   Database connection used for setting sitemap data.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $logger,
    Connection $database,
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $logger;
    $this->database = $database;
  }

  /**
   * Create the default homepage.
   */
  public function createHomepage() {
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
    $this->setSimplesitemapNoIndex($nid);
  }

  /**
   * Create the default 404 page.
   */
  public function create404Page() {
    $node = Node::create([
      'type' => 'basic_page',
      'title' => 'Page Not Found',
      'path' => ['alias' => '/404', 'pathauto' => PathautoState::SKIP],
      'body' => 'The page you are looking for appears to have been moved, deleted, or does not exist.',
    ]);
    $node->enforceIsNew()->save();
    $nid = $node->id();
    $this->configFactory->getEditable('system.site')->set('page.404', '/node/' . $nid)->save();
    $this->setSimplesitemapNoIndex($nid);
  }

  /**
   * Make sure sitemap knows about new default content.
   *
   * @param string $nid
   *   The id of the node.
   */
  private function setSimplesitemapNoIndex($nid) {
    // This method may not be part of the public API but there is no other clean
    // way of doing this. Simple XML Sitemap still uses a custom
    // `simple_sitemap_entity_overrides` DB table to set these overrides.
    // There's an issue to store these overrides as fields on the entity
    // but it's been open since 2019 (!!!):
    // https://www.drupal.org/project/simple_sitemap/issues/3034070
    // BEWARE OF BREAKING CHANGES to Simple XML Sitemap.
    if ($this->database->schema()->tableExists('simple_sitemap_entity_overrides')) {
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
