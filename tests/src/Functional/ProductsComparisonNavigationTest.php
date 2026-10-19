<?php

declare(strict_types=1);

namespace Drupal\Tests\products_comparison\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests configuration navigation and settings form access.
 */
#[Group('products_comparison')]
#[RunTestsInSeparateProcesses]
final class ProductsComparisonNavigationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['products_comparison', 'metsis_drupal'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The default METSIS View has pre-existing BEF schema gaps.
   *
   * @var string[]
   */
  protected static $configSchemaCheckerExclusions = ['views.view.metsis_search'];

  /**
   * Installs language-dependent Solr config before the METSIS server.
   */
  protected function installModulesFromClassProperty(ContainerInterface $container): void {
    $container->get('module_installer')->install(['language', 'search_api_solr']);
    parent::installModulesFromClassProperty(\Drupal::getContainer());
  }

  /**
   * Tests the shared METNO category displays the configuration form link.
   */
  public function testConfigurationNavigation(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access administration pages',
      'administer site configuration',
    ]));

    $settings_path = Url::fromRoute('products_comparison.admin_config_form')->toString();
    $metno_path = Url::fromRoute('metsis_drupal.admin_metno')->toString();
    $this->assertSame('/admin/config/metno', $metno_path);
    $this->assertTrue($this->container->get('router.admin_context')->isAdminRoute(
      $this->container->get('router.route_provider')->getRouteByName('products_comparison.admin_config_form')
    ));

    $this->drupalGet('admin/config');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists($metno_path);
    $this->assertSession()->linkByHrefExists($settings_path);

    $this->drupalGet($metno_path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Products comparison configuration');
    $this->assertSession()->linkByHrefExists($settings_path);
    $this->clickLink('Products comparison configuration');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals($settings_path);
    $this->assertSession()->fieldExists('defzoom');
    $this->assertSession()->fieldExists('lon');
    $this->assertSession()->fieldExists('lat');
    $this->assertSession()->fieldExists('rows');
    $this->assertSession()->buttonExists('Save configuration');

    $menu_links = $this->container->get('plugin.manager.menu.link');
    $this->assertSame('metsis_drupal.admin_metno', $menu_links->createInstance('products_comparison.admin_config_form')->getParent());
    $this->assertCount(1, $menu_links->loadLinksByRoute('metsis_drupal.admin_metno'));
    $this->assertFalse($menu_links->hasDefinition('metno.group.admin'));
    $this->assertFalse($menu_links->hasDefinition('metsis.group.admin'));
    $this->assertSame('<nolink>', $menu_links->createInstance('metsis.services')->getRouteName());
    $this->assertSame('metsis.services', $menu_links->createInstance('products_comparison')->getParent());
  }

  /**
   * Tests the settings form requires administration permission.
   */
  public function testSettingsAccess(): void {
    $settings_path = Url::fromRoute('products_comparison.admin_config_form')->toString();
    $this->drupalGet($settings_path);
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->drupalCreateUser(['access content']));
    $this->drupalGet($settings_path);
    $this->assertSession()->statusCodeEquals(403);
  }

}
