<?php

namespace Drupal\products_comparison\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\products_comparison\Service\ProductSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for the products comparison tool.
 *
 * {@inheritdoc}
 */
class ProductsComparisonController extends ControllerBase {

  /**
   * The logger channel used by this controller.
   */
  const LOG_CHANNEL = 'products_comparison';

  /**
   * Regular expression matching a valid Sentinel-2 MGRS tile identifier.
   *
   * E.g. "T32VNM": the leading "T", a two digit UTM zone, a latitude band
   * letter and a two letter 100km grid square identifier.
   */
  const TILE_PATTERN = '/^T\d{2}[A-Z]{3}$/';

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The route provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $router;

  /**
   * The product search service.
   */
  protected ProductSearchService $productSearch;

  /**
   * Constructs a ProductsComparisonController.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack service.
   * @param \Drupal\Core\Routing\RouteProviderInterface $router
   *   The route provider service.
   * @param \Drupal\products_comparison\Service\ProductSearchService $product_search
   *   The product search service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory service.
   */
  public function __construct(
    RequestStack $request,
    RouteProviderInterface $router,
    ProductSearchService $product_search,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->request = $request;
    $this->router = $router;
    $this->productSearch = $product_search;
    $this->configFactory = $config_factory;
    $this->setLoggerFactory($logger_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('router.route_provider'),
      $container->get('products_comparison.product_search'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Render the products comparison map.
   */
  public function render() {

    // $config = $this->config('system.site');
    $site_name = $this->request->getCurrentRequest()->getHost();
    // dpm($site_name);
    /* Handle .ddev.site special for local development */
    if (str_contains($site_name, 'ddev.site')) {
      $site_name = 'default';
    }
    $module_config = $this->config('products_comparison.configuration');
    $helptext = Markup::create($module_config->get('helptext')['value']);
    $lat = $module_config->get('lat');
    $lon = $module_config->get('lon');
    $defzoom = $module_config->get('defzoom');

    // Get the route path of the ajax callbacks.
    $route = $this->router->getRouteByName('products_comparison.products');
    $path = $route->getPath();
    $tiles_array = [
      "T25WEQ", "T25WER", "T25WES", "T25WET", "T25WEU", "T25WEV", "T25WFQ", "T25WFR", "T25WFS", "T25WFT", "T25WFU", "T25WFV", "T25XEA", "T25XEB", "T25XEC", "T25XED", "T25XEE", "T25XEF", "T25XEG", "T25XEH", "T25XEJ", "T25XEK", "T25XEL", "T25XEM", "T25XEN", "T25XFA", "T26VNR", "T26VPR", "T26WMA", "T26WMB", "T26WMC", "T26WMD", "T26WME", "T26WMV", "T26WNA", "T26WNB", "T26WNC", "T26WND", "T26WNE", "T26WNS", "T26WNT", "T26WNU", "T26WNV", "T26WPA", "T26WPB", "T26WPC", "T26WPD", "T26WPE", "T26WPS", "T26WPT", "T26WPU", "T26WPV", "T26XMF", "T26XMG", "T26XMH", "T26XMJ", "T26XMK", "T26XML", "T26XMM", "T26XMN", "T26XMP", "T26XMQ", "T26XMR", "T26XNF", "T26XNG", "T26XNH", "T26XNJ", "T26XNK", "T26XNL", "T26XNM", "T26XNN", "T26XNP", "T26XNQ", "T26XNR", "T26XNS", "T26XNT", "T26XPF", "T27VUL", "T27VVK", "T27VVL", "T27VWK", "T27VWL", "T27VXK", "T27VXL", "T27WVM", "T27WVN", "T27WVP", "T27WVQ", "T27WVR", "T27WVS", "T27WVT", "T27WVU", "T27WVV", "T27WWM", "T27WWN", "T27WWP", "T27WWQ", "T27WWR", "T27WWS", "T27WWT", "T27WWU", "T27WWV", "T27WXM", "T27WXN", "T27WXP", "T27WXQ", "T27WXR", "T27WXS", "T27WXT", "T27WXU", "T27WXV", "T27XVA", "T27XVB", "T27XVC", "T27XVD", "T27XVE", "T27XVF", "T27XVG", "T27XVH", "T27XVJ", "T27XVK", "T27XVL", "T27XWA", "T27XWB", "T27XWC", "T27XWD", "T27XWE", "T27XWF", "T27XWG", "T27XWH", "T27XWJ", "T27XWK", "T27XWL", "T27XWM", "T27XWN", "T27XXA", "T28VCP", "T28VCQ", "T28VCR", "T28VDJ", "T28VDK", "T28VDP", "T28VDQ", "T28VDR", "T28VEJ", "T28VEK", "T28VEP", "T28VEQ", "T28VER", "T28VFJ", "T28VFK", "T28VFL", "T28VFM", "T28VFP", "T28VFQ", "T28VFR", "T28WDA", "T28WDB", "T28WDC", "T28WDD", "T28WDE", "T28WDS", "T28WDT", "T28WDU", "T28WDV", "T28WEC", "T28WED", "T28WEE", "T28WES", "T28WET", "T28WEU", "T28WEV", "T28WFC", "T28WFD", "T28WFE", "T28WFS", "T28WFT", "T28WFU", "T28XDF", "T28XDG", "T28XDH", "T28XDJ", "T28XDK", "T28XDL", "T28XDM", "T28XDN", "T28XDP", "T28XDQ", "T28XDR", "T28XEF", "T28XEG", "T28XEH", "T28XEJ", "T28XEK", "T28XEL", "T28XEM", "T28XEN", "T28XEP", "T28XEQ", "T28XER", "T28XES", "T28XFF", "T29VLC", "T29VLD", "T29VLE", "T29VLF", "T29VLG", "T29VLH", "T29VLJ", "T29VLK", "T29VLL", "T29VMC", "T29VMD", "T29VME", "T29VMF", "T29VMG", "T29VMH", "T29VMJ", "T29VMK", "T29VML", "T29VNC", "T29VND", "T29VNE", "T29VNF", "T29VNG", "T29VNH", "T29VNJ", "T29VNK", "T29VNL", "T29VPC", "T29VPD", "T29VPE", "T29VPF", "T29VPG", "T29VPH", "T29VPJ", "T29VPK", "T29WMM", "T29WMN", "T29WMP", "T29WMT", "T29WMU", "T29WMV", "T29WNM", "T29WNN", "T29WNT", "T29WNU", "T29WNV", "T29WPS", "T29WPT", "T29WPU", "T29XMA", "T29XMB", "T29XMC", "T29XMD", "T29XME", "T29XMF", "T29XMG", "T29XMH", "T29XMJ", "T29XMK", "T29XML", "T29XNA", "T29XNB", "T29XND", "T29XNE", "T29XNF", "T29XNG", "T29XNH", "T29XNJ", "T29XNK", "T29XNL", "T30VUH", "T30VUJ", "T30VUK", "T30VUL", "T30VUM", "T30VUN", "T30VUP", "T30VUQ", "T30VVH", "T30VVJ", "T30VVK", "T30VVL", "T30VVM", "T30VVN", "T30VVP", "T30VVQ", "T30VWH", "T30VWJ", "T30VWK", "T30VWL", "T30VWM", "T30VWN", "T30VWP", "T30VWQ", "T30VWR", "T30VXH", "T30VXJ", "T30VXK", "T30VXL", "T30VXM", "T30VXN", "T30VXP", "T30VXQ", "T30VXR", "T30WVB", "T30WVC", "T30WVD", "T30WWB", "T30WWC", "T30WWD", "T30WWS", "T30WXS", "T30XVM", "T30XVN", "T30XVP", "T30XVQ", "T30XVR", "T30XWN", "T30XWP", "T30XWQ", "T30XWR", "T30XWS", "T31VCC", "T31VCD", "T31VCE", "T31VCF", "T31VCG", "T31VCH", "T31VCJ", "T31VCK", "T31VCL", "T31VDC", "T31VDD", "T31VDE", "T31VDF", "T31VDG", "T31VDH", "T31VDJ", "T31VDK", "T31VDL", "T31VEC", "T31VED", "T31VEE", "T31VEF", "T31VEG", "T31VEH", "T31VEJ", "T31VEK", "T31VEL", "T31VFC", "T31VFL", "T31WDM", "T31WDN", "T31WDP", "T31WEM", "T31WEN", "T31WEP", "T31WEQ", "T31WER", "T31WES", "T31WFM", "T31WFN", "T31WFP", "T31WFQ", "T31WFR", "T31WFS", "T31WFT", "T31WFU", "T31WGV", "T31XDD", "T31XDE", "T31XDF", "T31XDG", "T31XDH", "T31XDJ", "T31XDK", "T31XDL", "T31XED", "T31XEE", "T31XEF", "T31XEG", "T31XEH", "T31XEJ", "T31XEK", "T31XEL", "T31XEM", "T31XEN", "T31XFD", "T31XFE", "T31XFF", "T31XFG", "T31XFH", "T31XFJ", "T31XFK", "T31XFL", "T32VKJ", "T32VKK", "T32VKL", "T32VKM", "T32VKN", "T32VKP", "T32VKQ", "T32VKR", "T32VLH", "T32VLJ", "T32VLK", "T32VLL", "T32VLM", "T32VLN", "T32VLP", "T32VLQ", "T32VLR", "T32VMH", "T32VMJ", "T32VMK", "T32VML", "T32VMM", "T32VMN", "T32VMP", "T32VMQ", "T32VMR", "T32VNH", "T32VNJ", "T32VNK", "T32VNL", "T32VNM", "T32VNN", "T32VNP", "T32VNQ", "T32VNR", "T32VPH", "T32VPJ", "T32VPK", "T32VPL", "T32VPM", "T32VPN", "T32VPP", "T32VPQ", "T32VPR", "T32WMA", "T32WMB", "T32WMC", "T32WMD", "T32WMS", "T32WMT", "T32WMU", "T32WMV", "T32WNA", "T32WNB", "T32WNC", "T32WND", "T32WNE", "T32WNS", "T32WNT", "T32WNU", "T32WNV", "T32WPA", "T32WPB", "T32WPC", "T32WPD", "T32WPE", "T32WPS", "T32WPT", "T32WPU", "T32WPV", "T33VUC", "T33VUD", "T33VUE", "T33VUF", "T33VUG", "T33VUH", "T33VUJ", "T33VUK", "T33VUL", "T33VVC", "T33VVD", "T33VVE", "T33VVF", "T33VVG", "T33VVH", "T33VVJ", "T33VVK", "T33VVL", "T33VWC", "T33VWD", "T33VWE", "T33VWF", "T33VWG", "T33VWH", "T33VWJ", "T33VWK", "T33VWL", "T33VXC", "T33VXD", "T33VXE", "T33VXF", "T33VXG", "T33VXH", "T33VXJ", "T33VXK", "T33VXL", "T33WUV", "T33WVM", "T33WVN", "T33WVP", "T33WVQ", "T33WVR", "T33WVS", "T33WVT", "T33WVU", "T33WVV", "T33WWM", "T33WWN", "T33WWP", "T33WWQ", "T33WWR", "T33WWS", "T33WWT", "T33WWU", "T33WWV", "T33WXM", "T33WXN", "T33WXP", "T33WXQ", "T33WXR", "T33WXS", "T33WXT", "T33WXU", "T33WXV", "T33WYV", "T33XUC", "T33XUD", "T33XUE", "T33XUF", "T33XVA", "T33XVB", "T33XVC", "T33XVD", "T33XVE", "T33XVF", "T33XVG", "T33XVH", "T33XVJ", "T33XVK", "T33XVL", "T33XVM", "T33XVN", "T33XWA", "T33XWB", "T33XWC", "T33XWD", "T33XWE", "T33XWF", "T33XWG", "T33XWH", "T33XWJ", "T33XWK", "T33XWL", "T33XWM", "T33XWN", "T33XXA", "T33XXB", "T33XXC", "T33XXD", "T33XXE", "T33XXF", "T33XXG", "T33XXH", "T33XXJ", "T33XXK", "T33XXL", "T33XYA", "T34VCH", "T34VCJ", "T34VCK", "T34VCL", "T34VCM", "T34VCN", "T34VCP", "T34VCQ", "T34VCR", "T34VDH", "T34VDJ", "T34VDK", "T34VDL", "T34VDM", "T34VDN", "T34VDP", "T34VDQ", "T34VDR", "T34VEH", "T34VEJ", "T34VEK", "T34VEL", "T34VEM", "T34VEN", "T34VEP", "T34VEQ", "T34VER", "T34VFH", "T34VFJ", "T34VFK", "T34VFL", "T34VFM", "T34VFN", "T34VFP", "T34VFQ", "T34VFR", "T34WDA", "T34WDB", "T34WDC", "T34WDD", "T34WDS", "T34WDT", "T34WDU", "T34WDV", "T34WEA", "T34WEB", "T34WEC", "T34WED", "T34WEE", "T34WES", "T34WET", "T34WEU", "T34WEV", "T34WFA", "T34WFB", "T34WFC", "T34WFD",
    ];

    return [
      '#theme' => 'products_comparison-template',
      '#site_name' => $site_name,
      '#tiles' => $tiles_array,
      '#helptext' => $helptext,
      '#attached' => [
        'library' => [
          'products_comparison/products_comparison',
        ],

        'drupalSettings' => [
          'products_comparison' => [
            'zoomv' => $defzoom,
            'lat' => $lat,
            'lon' => $lon,
            'time_path' => $path,
            'site_name' => $site_name,
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Returns a page title.
   */
  public function getTitle() {
    // Get current language code.
    $language = $this->languageManager()->getCurrentLanguage()->getId();
    $title = '';
    switch ($language) {
      case 'nb':
        $title = 'Sentinel-2 produktsammenlikning';
        break;

      case 'en':
        $title = 'Sentinel-2 products comparison';
        break;
    }
    return $title;
  }

  /**
   * Get the matching products for a tile.
   *
   * Handles both plain XHR/fetch callers (JSON response) and HTMX callers
   * (an HTML fragment of <option> elements suitable for swapping directly
   * into the "Date - time" select elements). HTMX requests are detected via
   * the "HX-Request" header that htmx.org sets on every request it makes.
   *
   * Each matching Solr document is returned as an independent selectable
   * product, matching the original wildcard-query behavior.
   */
  public function getProductTime($tile) {
    $log = $this->getLogger(self::LOG_CHANNEL);
    $current_request = $this->request->getCurrentRequest();
    $is_htmx = $current_request->headers->get('HX-Request') === 'true'
      && !str_contains((string) $current_request->headers->get('Accept'), 'application/json');

    // Validate the tile parameter before it is used to build a Solr query.
    if (!is_string($tile) || !preg_match(self::TILE_PATTERN, $tile)) {
      $log->warning('Rejected invalid tile parameter: @tile', ['@tile' => (string) $tile]);
      return $this->buildErrorResponse('Invalid tile identifier.', 400, $is_htmx);
    }

    $cloud_coverage = (string) $current_request->query->get('cloud', 'None');

    // Cloud coverage is expressed as a fraction between 0 and 1 (see the
    // "cloudSelect" options in the products_comparison template), or "None"
    // when no filtering is requested.
    if ($cloud_coverage !== 'None' && (!is_numeric($cloud_coverage) || $cloud_coverage < 0 || $cloud_coverage > 1)) {
      $log->warning('Rejected invalid cloud coverage parameter: @cloud', ['@cloud' => $cloud_coverage]);
      return $this->buildErrorResponse('Invalid cloud coverage value.', 400, $is_htmx);
    }

    $module_config = $this->config('products_comparison.configuration');
    $rows = max(1, (int) $module_config->get('rows'));

    try {
      $groups = $this->productSearch->search($tile, $cloud_coverage, $rows);
    }
    catch (\Throwable $e) {
      $log->error('METSIS Solr query failed: @message', ['@message' => $e->getMessage()]);
      return $this->buildErrorResponse('Unable to retrieve products at this time.', 500, $is_htmx);
    }

    if ($is_htmx) {
      return $this->buildOptionsResponse($groups);
    }

    $response = new AjaxResponse();
    $response->setData($groups);
    return $response;
  }

  /**
   * Renders products as <option> elements for an HTMX swap.
   *
   * @param array<int, array<string, mixed>> $products
   *   Normalized products returned by the search service.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTML fragment response.
   */
  protected function buildOptionsResponse(array $products) {
    $html = '<option value="None" selected="selected">YYYY/MM/DD - THHMMSS</option>' . "\n";

    foreach ($products as $product) {
      $date = $product['date'];
      $label = sprintf('%s/%s/%s - %s', $date['year'], $date['month'], $date['day'], $date['time']);
      $resources = json_encode($product['resources']);
      $html .= sprintf(
        "<option value=\"%s\" data-resources='%s'>%s</option>\n",
        htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($resources, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
      );
    }

    $response = new Response($html);
    $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
    return $response;
  }

  /**
   * Builds an error response appropriate for the caller (HTMX or JSON).
   *
   * @param string $message
   *   The error message.
   * @param int $status
   *   The HTTP status code.
   * @param bool $is_htmx
   *   Whether the request came from HTMX.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The error response.
   */
  protected function buildErrorResponse($message, $status, $is_htmx) {
    if ($is_htmx) {
      $response = new Response(
        sprintf('<option value="None" selected="selected">%s</option>', htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
        $status
      );
      $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
      return $response;
    }

    $response = new AjaxResponse();
    $response->setStatusCode($status);
    $response->setData(['error' => $message]);
    return $response;
  }

}
