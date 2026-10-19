# S2 Products Comparison for Drupal 10/11

Drupal tool for comparsion of two Sentinel-2 products on a OpenLayers map.

Software metadata provided in the codemeta.json file.

### Authors
- Author: Lara Ferrighi, <laraf@met.no>
- Author: Magnar Eivind Martinsen, <magnarem@met.no>

## Installation

Install using composer on the sites:

* If installing on sites running in MET infrastructure, run `composer require "metno/products_comparison:^2"`.
* For other installations download or clone this module and add to the modules directory on your site installation.
* Enable the module on `/admin/modules` or use `drush en products_comparison.
Configure the module under **Configuration > METNO > Products comparison configuration**
at `/admin/config/metno/products_comparison`. The METNO group is provided by the
required `metsis_drupal` module.

After updating routing or menu link definitions, rebuild Drupal's caches with
`drush cr` (or `ddev drush cr` in the development environment).

## Form styling

The Preact controls use module-scoped styles in
[`js/comparison-app/src/style.css`](js/comparison-app/src/style.css), including
dropdowns, Plot/Hide buttons, disabled states, and keyboard focus indicators.
The controls use a light surface with explicit text colors so they remain
readable independently of the site's theme. Product cards stack when their
container is narrow, including when placed beside a theme sidebar.

Rebuild the production assets with `npm run build` from `js/comparison-app`
after changing the stylesheet, then rebuild Drupal's caches. Verify the controls
at desktop and mobile widths, including keyboard navigation and enabled/disabled
Plot buttons. Include the generated JavaScript, CSS, and Vite manifest in the
change; source maps are ignored.

## Testing

Inside the web container, run the custom-module suite without a `ddev` prefix:

```bash
vendor/bin/phpunit --bootstrap web/core/tests/bootstrap.php web/modules/custom
```

The navigation browser tests install Language and Search API Solr before
METSIS so Drupal installs the English Solr field types required by the default
server configuration. The default METSIS View is excluded from schema checking
because of existing Better Exposed Filters schema gaps; other configuration
remains checked.

## Setting up development environment

This repository/module uses [ddev](https://ddev.com) with [ddev-drupal-contrib](https://github.com/ddev/ddev-drupal-contrib) add-on.

This project also uses [vite](https://vite.dev) togheter with the drupal contrib [vite](https://drupal.org/project/vite)-module for streamlining development and building of the OpenLayers map Javascript application.

### Setting up ddev

1. Make sure you have [ddev](https://ddev.com)
2. Clone this repository
3. Create a file `.ddev/.env.local` with the environment variable `INTERNAL_SATIS_REPO` pointing to our internal composer satis repository.,
3. Run `ddev start`
4. Run `ddev poser`
5. Run `ddev symlink-project`
6. Run `ddev config --update`
6. Run `ddev restart`
7. Install the development drupal site: `ddev drush site:install -y`, or use the Web UI install by executin `ddev launch`
8. Install search_api_solr: `ddev drush en search_api_solr`
9. Install metsis_drupal dependency: `ddev drush en metsis_drupal`
10. Install this module: `ddev drush en products_comparison`


### Devlopment of openlayers app with vite
To Get vite working with drupal and use HMR using deleopement in vite dev mode, do the following:

1. Add a file called `settings.local.php` in `web/sites/default/` folder with the contents:
```php
<?php
/**
 * Set Ddev vite developement server.
 */
$settings['vite'] = [
  'devServerUrl' => $_ENV['DDEV_PRIMARY_URL'] . ':5173',
];
```
2. Edit `web/sites/default/settings.php`and comment out so you have the following lines in the file:
```php
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
```
3. Clear the cache with `ddev drush cr`

#### Starting the vite devserver
1. run `ddev ssh`
2. `cd js/comparison-app`
3. To start the vite dev server, run `npm run dev`. You should now be able to edit the app and changes will automatically be reflected on the site.
4. To build the final dist, run `npm run build`

#### Commiting changes
When commiting changes, if the `js/comparison-app/src/main.js` has changed do the following:

1. Run the `npm run build` as described in the [previous section](#starting-the-vite-devserver).
2. Update the version string in `js/comparison-app/package.json`.
3. Update to the same version string in `products_comparison.libraries.yml`.
4. Bump the version in `products_comparison.info.yml`
5. Add, commit and push the changes.
