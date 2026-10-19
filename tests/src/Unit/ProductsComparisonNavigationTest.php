<?php

declare(strict_types=1);

namespace Drupal\Tests\products_comparison\Unit;

use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the module's routing and navigation definitions.
 */
#[Group('products_comparison')]
final class ProductsComparisonNavigationTest extends UnitTestCase {

  /**
   * Tests configuration links consistently target the existing settings form.
   */
  public function testConfigurationNavigation(): void {
    $routes = $this->loadDefinition('routing');
    $links = $this->loadDefinition('links.menu');
    $tasks = $this->loadDefinition('links.task');
    $info = $this->loadDefinition('info');
    $translation = $this->loadDefinition('config_translation');
    $settings_route = 'products_comparison.admin_config_form';

    $this->assertSame('metsis_drupal.admin_metno', $links[$settings_route]['parent']);
    $this->assertSame($settings_route, $links[$settings_route]['route_name']);
    $this->assertSame([
      'path' => '/admin/config/metno/products_comparison',
      'defaults' => [
        '_form' => '\Drupal\products_comparison\Form\ProductsComparisonConfigurationForm',
        '_title' => 'Sentinel-2 products comparison configuration',
      ],
      'requirements' => ['_permission' => 'administer site configuration'],
      'options' => ['_admin_route' => TRUE],
    ], $routes[$settings_route]);
    $this->assertArrayNotHasKey('system.admin_config_metno', $routes);
    $this->assertArrayNotHasKey('metno.group.admin', $links);
    $this->assertArrayNotHasKey('metsis.group.admin', $links);
    $this->assertSame($settings_route, $info['configure']);
    $this->assertSame($settings_route, $tasks['products_comparison.configuration']['route_name']);
    $this->assertSame($settings_route, $tasks['products_comparison.configuration']['base_route']);
    $this->assertSame($settings_route, $translation['products_comparison.configuration']['base_route_name']);
    $this->assertSame(['products_comparison.configuration'], $translation['products_comparison.configuration']['names']);
  }

  /**
   * Tests the Services heading uses a non-link without changing its child.
   */
  public function testServicesNavigation(): void {
    $links = $this->loadDefinition('links.menu');

    $this->assertSame('<nolink>', $links['metsis.services']['route_name']);
    $this->assertSame('main', $links['metsis.services']['menu_name']);
    $this->assertSame('metsis.services', $links['products_comparison']['parent']);
    $this->assertSame('products_comparison', $links['products_comparison']['route_name']);
    $this->assertArrayNotHasKey('metsis.services', $this->loadDefinition('routing'));
  }

  /**
   * Tests the comparison page and both callback route contracts are preserved.
   */
  public function testPublicRoutes(): void {
    $routes = $this->loadDefinition('routing');
    $controller = '\Drupal\products_comparison\Controller\ProductsComparisonController::';

    $this->assertSame([
      'path' => '/services/comparison',
      'defaults' => [
        '_controller' => $controller . 'render',
        '_title_callback' => $controller . 'getTitle',
      ],
      'requirements' => ['_permission' => 'access content'],
    ], $routes['products_comparison']);

    foreach ([
      'date' => 'Products ajax callback',
      'products' => 'Products comparison options',
    ] as $callback => $title) {
      $this->assertSame([
        'path' => '/services/comparison/' . $callback . '/{tile}',
        'methods' => ['GET'],
        'defaults' => [
          '_controller' => $controller . 'getProductTime',
          '_title' => $title,
        ],
        'options' => [
          '_htmx_route' => TRUE,
          'query' => ['cloud' => 'None'],
        ],
        'requirements' => [
          '_permission' => 'access content',
          'tile' => '^T\d{2}[A-Z]{3}$',
        ],
      ], $routes['products_comparison.' . $callback]);
    }
  }

  /**
   * Loads a YAML definition from the module root.
   */
  private function loadDefinition(string $suffix): array {
    return Yaml::parseFile(dirname(__DIR__, 3) . '/products_comparison.' . $suffix . '.yml');
  }

}
