<?php

declare(strict_types=1);

namespace Drupal\products_comparison\Service;

use Drupal\metsis_drupal\Service\SolrConnectorProvider;
use Drupal\metsis_drupal\Service\SolrQueryFactory;

/**
 * Searches Sentinel-2 products for the comparison application.
 */
final class ProductSearchService {

  /**
   * Fields needed by the comparison map.
   *
   * @var string[]
   */
  private const FIELDS = [
    'id',
    'title',
    'data_access_url_ogc_wms',
    'geographic_extent_rectangle_south',
    'geographic_extent_rectangle_north',
    'geographic_extent_rectangle_west',
    'geographic_extent_rectangle_east',
  ];

  /**
   * Constructs the product search service.
   */
  public function __construct(
    private readonly SolrQueryFactory $queryFactory,
    private readonly SolrConnectorProvider $connectorProvider,
  ) {}

  /**
   * Finds Sentinel-2 products matching a tile wildcard query.
   *
   * @return array<int, array<string, mixed>>
   *   Normalized products.
   */
  public function search(string $tile, string $cloudCoverage, int $rows): array {
    $query = $this->queryFactory->createSelectQuery();
    $query->setFields(self::FIELDS)
      ->setQuery('title:S2*' . $tile . '*')
      ->setStart(0)
      ->setRows($rows);
    $query->addSort('temporal_extent_start_date', $query::SORT_DESC);
    $query->createFilterQuery('active')
      ->setQuery('metadata_status:Active');
    $query->createFilterQuery('nbs')
      ->setQuery('collection:NBS');

    $query->createFilterQuery('wms')
      ->setQuery('data_access_url_ogc_wms:[* TO *]');

    if ($cloudCoverage !== 'None') {
      $query->createFilterQuery('cloud_coverage')
        ->setQuery('platform_ancillary_cloud_coverage:[0 TO ' . $cloudCoverage . ']');
    }

    $products = [];
    foreach ($this->connectorProvider->getConnector()->execute($query) as $document) {
      $fields = $document->getFields();
      $title = $this->firstValue($fields['title'] ?? NULL);
      $url = $this->firstValue($fields['data_access_url_ogc_wms'] ?? NULL);
      $time = $this->extractTime($title);
      if ($title === NULL || $url === NULL || $time === NULL) {
        continue;
      }

      $latlon = $this->extractLatLon($fields);
      $products[] = [
        'id' => $title,
        'url' => $url,
        'date' => [
          'year' => substr($time, 0, 4),
          'month' => substr($time, 4, 2),
          'day' => substr($time, 6, 2),
          'time' => substr($time, 8, 7),
        ],
        'latlon' => $latlon,
        'resources' => [
          [
            'id' => $title,
            'url' => $url,
            'latlon' => $latlon,
          ],
        ],
      ];
    }

    return $products;
  }

  /**
   * Extracts the acquisition timestamp from a Sentinel-2 title.
   */
  private function extractTime(?string $title): ?string {
    if ($title === NULL) {
      return NULL;
    }
    $parts = explode('_', $title);
    $time = str_contains($title, 'OPER') ? ($parts[7] ?? NULL) : ($parts[6] ?? NULL);
    return is_string($time) && preg_match('/^\d{8}T\d{6}/', $time) ? $time : NULL;
  }

  /**
   * Calculates the center point of a product extent.
   *
   * @param array<string, mixed> $fields
   *   Solr fields.
   *
   * @return array{0: float, 1: float}|null
   *   Latitude/longitude or NULL when the extent is incomplete.
   */
  private function extractLatLon(array $fields): ?array {
    $north = $this->firstValue($fields['geographic_extent_rectangle_north'] ?? NULL);
    $south = $this->firstValue($fields['geographic_extent_rectangle_south'] ?? NULL);
    $east = $this->firstValue($fields['geographic_extent_rectangle_east'] ?? NULL);
    $west = $this->firstValue($fields['geographic_extent_rectangle_west'] ?? NULL);
    if (!is_numeric($north) || !is_numeric($south) || !is_numeric($east) || !is_numeric($west)) {
      return NULL;
    }
    return [(float) (($south + $north) / 2), (float) (($east + $west) / 2)];
  }

  /**
   * Returns a scalar from a possibly multivalued Solr field.
   */
  private function firstValue(mixed $value): mixed {
    return is_array($value) ? ($value[0] ?? NULL) : $value;
  }

}
