<?php

namespace Drupal\products_comparison\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteProvider;
use Drupal\search_api\Entity\Index;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for the products comparison tool.
 *
 * {@inheritdoc}
 */
class ProductsComparisonController extends ControllerBase {
  /**
   * The request stack service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The route provider service.
   *
   * @var Drupal\Core\Routing\RouteProvider
   */
  protected $router;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request, RouteProvider $route) {
    $this->request = $request;
    $this->router = $route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('router.route_provider')
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
    $route = $this->router->getRouteByName('products_comparison.date');
    $path = $route->getPath();
    $tiles_array = [
      "T25WEQ", "T25WER", "T25WES", "T25WET", "T25WEU", "T25WEV", "T25WFQ", "T25WFR", "T25WFS", "T25WFT", "T25WFU", "T25WFV", "T25XEA", "T25XEB", "T25XEC", "T25XED", "T25XEE", "T25XEF", "T25XEG", "T25XEH", "T25XEJ", "T25XEK", "T25XEL", "T25XEM", "T25XEN", "T25XFA", "T26VNR", "T26VPR", "T26WMA", "T26WMB", "T26WMC", "T26WMD", "T26WME", "T26WMV", "T26WNA", "T26WNB", "T26WNC", "T26WND", "T26WNE", "T26WNS", "T26WNT", "T26WNU", "T26WNV", "T26WPA", "T26WPB", "T26WPC", "T26WPD", "T26WPE", "T26WPS", "T26WPT", "T26WPU", "T26WPV", "T26XMF", "T26XMG", "T26XMH", "T26XMJ", "T26XMK", "T26XML", "T26XMM", "T26XMN", "T26XMP", "T26XMQ", "T26XMR", "T26XNF", "T26XNG", "T26XNH", "T26XNJ", "T26XNK", "T26XNL", "T26XNM", "T26XNN", "T26XNP", "T26XNQ", "T26XNR", "T26XNS", "T26XNT", "T26XPF", "T27VUL", "T27VVK", "T27VVL", "T27VWK", "T27VWL", "T27VXK", "T27VXL", "T27WVM", "T27WVN", "T27WVP", "T27WVQ", "T27WVR", "T27WVS", "T27WVT", "T27WVU", "T27WVV", "T27WWM", "T27WWN", "T27WWP", "T27WWQ", "T27WWR", "T27WWS", "T27WWT", "T27WWU", "T27WWV", "T27WXM", "T27WXN", "T27WXP", "T27WXQ", "T27WXR", "T27WXS", "T27WXT", "T27WXU", "T27WXV", "T27XVA", "T27XVB", "T27XVC", "T27XVD", "T27XVE", "T27XVF", "T27XVG", "T27XVH", "T27XVJ", "T27XVK", "T27XVL", "T27XWA", "T27XWB", "T27XWC", "T27XWD", "T27XWE", "T27XWF", "T27XWG", "T27XWH", "T27XWJ", "T27XWK", "T27XWL", "T27XWM", "T27XWN", "T27XXA", "T28VCP", "T28VCQ", "T28VCR", "T28VDJ", "T28VDK", "T28VDP", "T28VDQ", "T28VDR", "T28VEJ", "T28VEK", "T28VEP", "T28VEQ", "T28VER", "T28VFJ", "T28VFK", "T28VFL", "T28VFM", "T28VFP", "T28VFQ", "T28VFR", "T28WDA", "T28WDB", "T28WDC", "T28WDD", "T28WDE", "T28WDS", "T28WDT", "T28WDU", "T28WDV", "T28WEC", "T28WED", "T28WEE", "T28WES", "T28WET", "T28WEU", "T28WEV", "T28WFC", "T28WFD", "T28WFE", "T28WFS", "T28WFT", "T28WFU", "T28XDF", "T28XDG", "T28XDH", "T28XDJ", "T28XDK", "T28XDL", "T28XDM", "T28XDN", "T28XDP", "T28XDQ", "T28XDR", "T28XEF", "T28XEG", "T28XEH", "T28XEJ", "T28XEK", "T28XEL", "T28XEM", "T28XEN", "T28XEP", "T28XEQ", "T28XER", "T28XES", "T28XFF", "T29VLC", "T29VLD", "T29VLE", "T29VLF", "T29VLG", "T29VLH", "T29VLJ", "T29VLK", "T29VLL", "T29VMC", "T29VMD", "T29VME", "T29VMF", "T29VMG", "T29VMH", "T29VMJ", "T29VMK", "T29VML", "T29VNC", "T29VND", "T29VNE", "T29VNF", "T29VNG", "T29VNH", "T29VNJ", "T29VNK", "T29VNL", "T29VPC", "T29VPD", "T29VPE", "T29VPF", "T29VPG", "T29VPH", "T29VPJ", "T29VPK", "T29WMM", "T29WMN", "T29WMP", "T29WMT", "T29WMU", "T29WMV", "T29WNM", "T29WNN", "T29WNT", "T29WNU", "T29WNV", "T29WPS", "T29WPT", "T29WPU", "T29XMA", "T29XMB", "T29XMC", "T29XMD", "T29XME", "T29XMF", "T29XMG", "T29XMH", "T29XMJ", "T29XMK", "T29XML", "T29XNA", "T29XNB", "T29XND", "T29XNE", "T29XNF", "T29XNG", "T29XNH", "T29XNJ", "T29XNK", "T29XNL", "T30VUH", "T30VUJ", "T30VUK", "T30VUL", "T30VUM", "T30VUN", "T30VUP", "T30VUQ", "T30VVH", "T30VVJ", "T30VVK", "T30VVL", "T30VVM", "T30VVN", "T30VVP", "T30VVQ", "T30VWH", "T30VWJ", "T30VWK", "T30VWL", "T30VWM", "T30VWN", "T30VWP", "T30VWQ", "T30VWR", "T30VXH", "T30VXJ", "T30VXK", "T30VXL", "T30VXM", "T30VXN", "T30VXP", "T30VXQ", "T30VXR", "T30WVB", "T30WVC", "T30WVD", "T30WWB", "T30WWC", "T30WWD", "T30WWS", "T30WXS", "T30XVM", "T30XVN", "T30XVP", "T30XVQ", "T30XVR", "T30XWN", "T30XWP", "T30XWQ", "T30XWR", "T30XWS", "T31VCC", "T31VCD", "T31VCE", "T31VCF", "T31VCG", "T31VCH", "T31VCJ", "T31VCK", "T31VCL", "T31VDC", "T31VDD", "T31VDE", "T31VDF", "T31VDG", "T31VDH", "T31VDJ", "T31VDK", "T31VDL", "T31VEC", "T31VED", "T31VEE", "T31VEF", "T31VEG", "T31VEH", "T31VEJ", "T31VEK", "T31VEL", "T31VFC", "T31VFL", "T31WDM", "T31WDN", "T31WDP", "T31WEM", "T31WEN", "T31WEP", "T31WEQ", "T31WER", "T31WES", "T31WFM", "T31WFN", "T31WFP", "T31WFQ", "T31WFR", "T31WFS", "T31WFT", "T31WFU", "T31WGV", "T31XDD", "T31XDE", "T31XDF", "T31XDG", "T31XDH", "T31XDJ", "T31XDK", "T31XDL", "T31XED", "T31XEE", "T31XEF", "T31XEG", "T31XEH", "T31XEJ", "T31XEK", "T31XEL", "T31XEM", "T31XEN", "T31XFD", "T31XFE", "T31XFF", "T31XFG", "T31XFH", "T31XFJ", "T31XFK", "T31XFL", "T32VKJ", "T32VKK", "T32VKL", "T32VKM", "T32VKN", "T32VKP", "T32VKQ", "T32VKR", "T32VLH", "T32VLJ", "T32VLK", "T32VLL", "T32VLM", "T32VLN", "T32VLP", "T32VLQ", "T32VLR", "T32VMH", "T32VMJ", "T32VMK", "T32VML", "T32VMM", "T32VMN", "T32VMP", "T32VMQ", "T32VMR", "T32VNH", "T32VNJ", "T32VNK", "T32VNL", "T32VNM", "T32VNN", "T32VNP", "T32VNQ", "T32VNR", "T32VPH", "T32VPJ", "T32VPK", "T32VPL", "T32VPM", "T32VPN", "T32VPP", "T32VPQ", "T32VPR", "T32WMA", "T32WMB", "T32WMC", "T32WMD", "T32WMS", "T32WMT", "T32WMU", "T32WMV", "T32WNA", "T32WNB", "T32WNC", "T32WND", "T32WNE", "T32WNS", "T32WNT", "T32WNU", "T32WNV", "T32WPA", "T32WPB", "T32WPC", "T32WPD", "T32WPE", "T32WPS", "T32WPT", "T32WPU", "T32WPV", "T33VUC", "T33VUD", "T33VUE", "T33VUF", "T33VUG", "T33VUH", "T33VUJ", "T33VUK", "T33VUL", "T33VVC", "T33VVD", "T33VVE", "T33VVF", "T33VVG", "T33VVH", "T33VVJ", "T33VVK", "T33VVL", "T33VWC", "T33VWD", "T33VWE", "T33VWF", "T33VWG", "T33VWH", "T33VWJ", "T33VWK", "T33VWL", "T33VXC", "T33VXD", "T33VXE", "T33VXF", "T33VXG", "T33VXH", "T33VXJ", "T33VXK", "T33VXL", "T33WUV", "T33WVM", "T33WVN", "T33WVP", "T33WVQ", "T33WVR", "T33WVS", "T33WVT", "T33WVU", "T33WVV", "T33WWM", "T33WWN", "T33WWP", "T33WWQ", "T33WWR", "T33WWS", "T33WWT", "T33WWU", "T33WWV", "T33WXM", "T33WXN", "T33WXP", "T33WXQ", "T33WXR", "T33WXS", "T33WXT", "T33WXU", "T33WXV", "T33WYV", "T33XUC", "T33XUD", "T33XUE", "T33XUF", "T33XVA", "T33XVB", "T33XVC", "T33XVD", "T33XVE", "T33XVF", "T33XVG", "T33XVH", "T33XVJ", "T33XVK", "T33XVL", "T33XVM", "T33XVN", "T33XWA", "T33XWB", "T33XWC", "T33XWD", "T33XWE", "T33XWF", "T33XWG", "T33XWH", "T33XWJ", "T33XWK", "T33XWL", "T33XWM", "T33XWN", "T33XXA", "T33XXB", "T33XXC", "T33XXD", "T33XXE", "T33XXF", "T33XXG", "T33XXH", "T33XXJ", "T33XXK", "T33XXL", "T33XYA", "T34VCH", "T34VCJ", "T34VCK", "T34VCL", "T34VCM", "T34VCN", "T34VCP", "T34VCQ", "T34VCR", "T34VDH", "T34VDJ", "T34VDK", "T34VDL", "T34VDM", "T34VDN", "T34VDP", "T34VDQ", "T34VDR", "T34VEH", "T34VEJ", "T34VEK", "T34VEL", "T34VEM", "T34VEN", "T34VEP", "T34VEQ", "T34VER", "T34VFH", "T34VFJ", "T34VFK", "T34VFL", "T34VFM", "T34VFN", "T34VFP", "T34VFQ", "T34VFR", "T34WDA", "T34WDB", "T34WDC", "T34WDD", "T34WDS", "T34WDT", "T34WDU", "T34WDV", "T34WEA", "T34WEB", "T34WEC", "T34WED", "T34WEE", "T34WES", "T34WET", "T34WEU", "T34WEV", "T34WFA", "T34WFB", "T34WFC", "T34WFD", "T34WFE", "T34WFS", "T34WFT", "T34WFU", "T34WFV", "T35VLC", "T35VLD", "T35VLE", "T35VLF", "T35VLG", "T35VLH", "T35VLJ", "T35VLK", "T35VLL", "T35VMC", "T35VMD", "T35VME", "T35VMF", "T35VMG", "T35VMH", "T35VMJ", "T35VMK", "T35VML", "T35VNC", "T35VND", "T35VNE", "T35VNF", "T35VNG", "T35VNH", "T35VNJ", "T35VNK", "T35VNL", "T35VPC", "T35VPD", "T35VPE", "T35VPF", "T35VPG", "T35VPH", "T35VPJ", "T35VPK", "T35VPL", "T35WLV", "T35WMM", "T35WMN", "T35WMP", "T35WMQ", "T35WMR", "T35WMS", "T35WMT", "T35WMU", "T35WMV", "T35WNM", "T35WNN", "T35WNP", "T35WNQ", "T35WNR", "T35WNS", "T35WNT", "T35WNU", "T35WNV", "T35WPM", "T35WPN", "T35WPP", "T35WPQ", "T35WPR", "T35WPS", "T35WPT", "T35WPU", "T35WPV", "T35WQV", "T35XLA", "T35XLB", "T35XLC", "T35XLD", "T35XLE", "T35XLF", "T35XMA", "T35XMB", "T35XMC", "T35XMD", "T35XME", "T35XMF", "T35XMG", "T35XMH", "T35XMJ", "T35XMK", "T35XML", "T35XMM", "T35XMN", "T35XNA", "T35XNB", "T35XNC", "T35XND", "T35XNE", "T35XNF", "T35XNG", "T35XNH", "T35XNJ", "T35XNK", "T35XNL", "T35XNM", "T35XPC", "T35XPD", "T35XPE", "T35XPF", "T35XPG", "T35XPH", "T35XPJ", "T35XPK", "T35XPL", "T35XQA", "T36VUH", "T36VUJ", "T36VUK", "T36VUL", "T36VUM", "T36VUN", "T36VUP", "T36VUQ", "T36VUR", "T36VVH", "T36VVJ", "T36VVK", "T36VVL", "T36VVM", "T36VVN", "T36VVP", "T36VVQ", "T36VVR", "T36VWH", "T36VWJ", "T36VWK", "T36VWL", "T36VWM", "T36VWN", "T36VWP", "T36VWQ", "T36VWR", "T36VXJ", "T36VXK", "T36VXL", "T36VXM", "T36VXN", "T36VXP", "T36VXQ", "T36VXR", "T36WVA", "T36WVB", "T36WVC", "T36WVD", "T36WVS", "T36WVT", "T36WVU", "T36WVV", "T36WWA", "T36WWB", "T36WWC", "T36WWD", "T36WWE", "T36WWS", "T36WWT", "T36WWU", "T36WWV", "T36WXA", "T36WXB", "T36WXC", "T36WXD", "T36WXE", "T36WXS", "T36WXT", "T36WXU", "T36WXV", "T37VCD", "T37VCE", "T37VCF", "T37VCG", "T37VCH", "T37VCJ", "T37VCK", "T37VCL", "T37VDD", "T37VDE", "T37VDF", "T37VDG", "T37VDH", "T37VDJ", "T37VDK", "T37VDL", "T37VED", "T37VEE", "T37VEF", "T37VEG", "T37VEH", "T37VEJ", "T37VEK", "T37VEL", "T37VFD", "T37VFE", "T37VFF", "T37VFG", "T37VFH", "T37VFJ", "T37VFK", "T37VFL", "T37WCV", "T37WDM", "T37WDN", "T37WDP", "T37WDQ", "T37WDR", "T37WDS", "T37WDT", "T37WDU", "T37WDV", "T37WEM", "T37WEN", "T37WEP", "T37WEQ", "T37WER", "T37WES", "T37WET", "T37WEU", "T37WEV", "T37WFM", "T37WFN", "T37WFP", "T37WFQ", "T37WFR", "T37WFS", "T37WFT", "T37WFU", "T37WFV", "T37XCA", "T37XCD", "T37XCE", "T37XCF", "T37XDA", "T37XDD", "T37XDE", "T37XDF", "T37XDG", "T37XDH", "T37XDJ", "T37XDK", "T37XDL", "T37XDM", "T37XEE", "T37XEF", "T37XEG", "T37XEH", "T37XEJ", "T37XEK", "T37XEL", "T37XEM", "T37XEN", "T37XFA", "T38KLU", "T38VLJ", "T38VLK", "T38VLL", "T38VLM", "T38VLN", "T38VLP", "T38VLQ", "T38VLR", "T38VMJ", "T38VMK", "T38VML", "T38VMM", "T38VMN", "T38VMP", "T38VMQ", "T38VMR", "T38VNJ", "T38VNK", "T38VNL", "T38VNM", "T38VNN", "T38VNP", "T38VNQ", "T38VNR", "T38VPJ", "T38VPK", "T38VPL", "T38VPM", "T38VPN", "T38VPP", "T38VPQ", "T38VPR", "T38WMA", "T38WMB", "T38WMC", "T38WMD", "T38WME", "T38WMS", "T38WMT", "T38WMU", "T38WMV", "T38WNA", "T38WNB", "T38WNC", "T38WND", "T38WNE", "T38WNS", "T38WNT", "T38WNU", "T38WNV", "T38WPA", "T38WPB", "T38WPC", "T38WPD", "T38WPE", "T38WPS", "T38WPT", "T38WPU", "T38WPV", "T38XMF", "T38XMG", "T38XMH", "T38XMK", "T38XML", "T38XMM", "T38XMN", "T38XMP", "T38XMQ", "T38XMR", "T38XNF", "T38XNG", "T38XNH", "T38XNJ", "T38XNK", "T38XNL", "T38XNM", "T38XNN", "T38XNP", "T38XNQ", "T38XNR", "T38XNS", "T38XNT", "T38XPF", "T39VUD", "T39VUE", "T39VUF", "T39VUG", "T39VUH", "T39VUJ", "T39VUK", "T39VUL", "T39VVD", "T39VVE", "T39VVF", "T39VVG", "T39VVH", "T39VVJ", "T39VVK", "T39VVL", "T39VWD", "T39VWE", "T39VWF", "T39VWG", "T39VWH", "T39VWJ", "T39VWK", "T39VWL", "T39VXD", "T39VXE", "T39VXF", "T39VXG", "T39VXH", "T39VXJ", "T39VXK", "T39VXL", "T39WVM", "T39WVN", "T39WVP", "T39WVQ", "T39WVR", "T39WVS", "T39WVT", "T39WVU", "T39WVV", "T39WWM", "T39WWN", "T39WWP", "T39WWQ", "T39WWR", "T39WWS", "T39WWT", "T39WWU", "T39WWV", "T39WXM", "T39WXN", "T39WXP", "T39WXQ", "T39WXR", "T39WXS", "T39WXT", "T39WXU", "T39WXV", "T39XVA", "T39XVB", "T39XVC", "T39XVD", "T39XVE", "T39XVF", "T39XVG", "T39XVH", "T39XVJ", "T39XVK", "T39XVL", "T39XWA", "T39XWB", "T39XWC", "T39XWD", "T39XWE", "T39XWF", "T39XWG", "T39XWH", "T39XWJ", "T39XWK", "T39XWL", "T39XWM", "T39XXA", "T40VCJ", "T40VCK", "T40VCL", "T40VCM", "T40VCN", "T40VCP", "T40VCQ", "T40VCR", "T40VDJ", "T40VDK", "T40VDL", "T40VDM", "T40VDN", "T40VDP", "T40VDQ", "T40VDR", "T40VEJ", "T40VEK", "T40VEL", "T40VEM", "T40VEN", "T40VEP", "T40VEQ", "T40VER", "T40VFJ", "T40VFK", "T40VFL", "T40VFM", "T40VFN", "T40VFP", "T40VFQ", "T40VFR", "T40WDA", "T40WDB", "T40WDC", "T40WDD", "T40WDE", "T40WDS", "T40WDT", "T40WDU", "T40WDV", "T40WEA", "T40WEB", "T40WEC", "T40WED", "T40WEE", "T40WES", "T40WET", "T40WEU", "T40WEV", "T40WFA", "T40WFB", "T40WFC", "T40WFD", "T40WFE", "T40WFS", "T40WFT", "T40WFU", "T40WFV", "T40XDF", "T40XDG", "T40XDH", "T40XDJ", "T40XDK", "T40XDL", "T40XDM", "T40XDN", "T40XDP", "T40XDQ", "T40XDR", "T40XEF", "T40XEG", "T40XEH", "T40XEJ", "T40XEK", "T40XEL", "T40XEM", "T40XEN", "T40XEP", "T40XEQ", "T40XER", "T40XES", "T40XET", "T40XFF", "T41VLD", "T41VLE", "T41VLF", "T41VLG", "T41VLH", "T41VLJ", "T41VLK", "T41VLL", "T41VMD", "T41VME", "T41VMF", "T41VMG", "T41VMH", "T41VMJ", "T41VMK", "T41VML", "T41VND", "T41VNE", "T41VNF", "T41VNG", "T41VNH", "T41VNJ", "T41VNK", "T41VNL", "T41VPD", "T41VPE", "T41VPF", "T41VPG", "T41VPJ", "T41VPK", "T41VPL", "T41WMM", "T41WMN", "T41WMP", "T41WMQ", "T41WMR", "T41WMS", "T41WMT", "T41WMU", "T41WMV", "T41WNM", "T41WNN", "T41WNP", "T41WNQ", "T41WNR", "T41WNS", "T41WNT", "T41WNU", "T41WNV", "T41WPM", "T41WPN", "T41WPP", "T41WPQ", "T41WPR", "T41WPS", "T41WPT", "T41WPU", "T41WPV", "T41XMA", "T41XMB", "T41XMC", "T41XMD", "T41XME", "T41XMF", "T41XMG", "T41XMH", "T41XMJ", "T41XMK", "T41XML", "T41XNA", "T41XNB", "T41XNC", "T41XND", "T41XNE", "T41XNF", "T41XNG", "T41XNH", "T41XNJ", "T41XNK", "T41XNL", "T41XNM", "T41XPA", "T42VUJ", "T42VUK", "T42VUL", "T42VUM", "T42VUN", "T42VUP", "T42VUQ", "T42VUR", "T42VVJ", "T42VVK", "T42VVL", "T42VVM", "T42VVN", "T42VVP", "T42VVQ", "T42VVR", "T42VWJ", "T42VWK", "T42VWL", "T42VWM", "T42VWN", "T42VWP", "T42VWQ", "T42VWR", "T42VXJ", "T42VXK", "T42VXL", "T42VXM", "T42VXN", "T42VXP", "T42VXQ", "T42VXR", "T42WVA", "T42WVB", "T42WVC", "T42WVD", "T42WVE", "T42WVS", "T42WVT", "T42WVU", "T42WVV", "T42WWA", "T42WWB", "T42WWC", "T42WWD", "T42WWE", "T42WWS", "T42WWT", "T42WWU", "T42WWV", "T42WXA", "T42WXB", "T42WXC", "T42WXD", "T42WXE", "T42WXS", "T42WXT", "T42WXU", "T42WXV", "T42XVF", "T42XVG", "T42XVH", "T42XVJ", "T42XVK", "T42XVL", "T42XVM", "T42XVN", "T42XVP", "T42XVQ", "T42XVR", "T42XWF", "T42XWG", "T42XWH", "T42XWJ", "T42XWK", "T42XWL", "T42XWM", "T42XWN", "T42XWP", "T42XWQ", "T42XWR", "T42XWS", "T42XWT", "T42XXF", "T43VCD", "T43VCE", "T43VCF", "T43VCG", "T43VCH", "T43VCJ", "T43VCK", "T43VCL", "T43XDA", "T43XDB", "T43XDC", "T43XDD", "T43XDE", "T43XDF", "T43XDG", "T43XDH", "T43XDJ", "T43XDK", "T43XDL", "T47UNA", "T50LQP",
    ];

    return [
      '#type' => 'container',
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
   * Get the selected image from WMS.
   */
  public function selectImage($scr_name, $pr1, $pr2, $prinfo, $tile, $action, $tiles_array) {

    $output = '';
    $output .= "<form action=\"" . $scr_name . "\" method=\"get\">\n";
    $output .= "<table style=\"width:80%; margin-right:auto; margin-left:auto\">";
    $output .= "<tr>";
    $output .= "<th style=\"text-align: center; color: #222222\">";
    $output .= "Tile";
    $output .= "</th>";
    $output .= "<th style=\"text-align: center; color: #222222\">";
    $output .= "Date - Time";
    $output .= "</th>";
    $output .= "</tr>";

    $output .= "<tr>";
    // Set up the tile selection.
    $output .= "<th rowspan=\"2\">";
    $output .= "Select a Tile: <select class=\"drop-selection\" onchange=\"this.form.pr1.value='None'; this.form.pr2.value='None'; this.form.submit()\" name=\"tile\">\n";
    for ($p = 0; $p < count($tiles_array); $p++) {
      if ($tiles_array[$p] !== $tile) {
        $output .= "<option>" . $tiles_array[$p] . "</option>\n";
      }
      else {
        $output .= "<option selected=\"selected\">" . $tiles_array[$p] . "</option>\n";
      }
    }
    $output .= "<br>";
    $output .= "</select>\n";
    $output .= "</th>";

    // Set up the year selection.
    $output .= "<th>";
    $output .= "First Product: <span style=\"display:inline-block; width: 1.5em;\"></span><select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"pr1\">\n";
    for ($y = 0; $y < count($prinfo); $y++) {
      if ($prinfo[$y][1] !== $pr1) {
        $output .= "<option value=" . $prinfo[$y][1] . ">" . $prinfo[$y][0][0] . '/' . $prinfo[$y][0][1] . '/' . $prinfo[$y][0][2] . " - " . $prinfo[$y][0][3] . "</option>\n";
      }
      else {
        $output .= "<option value=" . $prinfo[$y][1] . " selected=\"selected\">" . $prinfo[$y][0][0] . '/' . $prinfo[$y][0][1] . '/' . $prinfo[$y][0][2] . " - " . $prinfo[$y][0][3] . "</option>\n";
        $address1 = $prinfo[$y][2];
        $latlon1 = [$prinfo[$y][3][0], $prinfo[$y][3][1]];
      }
    }
    $output .= "</select>\n";
    $output .= "</th>";

    $output .= "</tr>";

    $output .= "<tr>";

    // Set up the year selection.
    $output .= "<th>";
    $output .= "Second Product: <select class=\"drop-selection\" onchange=\"this.form.submit()\" name=\"pr2\">\n";
    for ($y = 0; $y < count($prinfo); $y++) {
      if ($prinfo[$y][1] !== $pr2) {
        $output .= "<option value=" . $prinfo[$y][1] . ">" . $prinfo[$y][0][0] . '/' . $prinfo[$y][0][1] . '/' . $prinfo[$y][0][2] . " - " . $prinfo[$y][0][3] . "</option>\n";
      }
      else {
        $output .= "<option value=" . $prinfo[$y][1] . " selected=\"selected\">" . $prinfo[$y][0][0] . '/' . $prinfo[$y][0][1] . '/' . $prinfo[$y][0][2] . " - " . $prinfo[$y][0][3] . "</option>\n";
        $address2 = $prinfo[$y][2];
        $latlon2 = [$prinfo[$y][3][0], $prinfo[$y][3][1]];
      }
    }
    $output .= "</select>\n";
    $output .= "</th>";

    $output .= "</tr>";

    if ($pr1 !== 'None' && $pr2 !== 'None') {
      $output .= "<tr>";
      $output .= "<td  style=\"text-align: center; font-size:15px\" colspan=\"2\">";
      $output .= "<strong>You are comparing Sentinel-2 products:</strong><br>";
      $output .= $pr1 . "<br>" . $pr2;
      $output .= "</td>";
      $output .= "</tr>";
    }
    $output .= "</table>";
    $output .= "</form>\n";

    return [$output, $address1, $address2, $latlon1, $latlon2];
  }

  /**
   * Find the product from yesterday.
   */
  public function findYesterday() {

    // Find default.
    $deftime  = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y")) - 24 * 60 * 60;
    $defdate  = getdate($deftime);
    $defyear  = sprintf("%4d", $defdate["year"]);
    $defmonth = sprintf("%02d", $defdate["mon"]);
    $defday   = sprintf("%02d", $defdate["mday"]);

    $defaults = [$defyear, $defmonth, $defday];

    return $defaults;
  }

  /**
   * Get the latest date for product.
   */
  public function getDate($year, $month, $day) {

    [$defyear, $defmonth, $defday] = $this->findYesterday();

    $date = [$year, $month, $day];

    $year  = sprintf("%4d", $date[0]);
    $month = sprintf("%02d", $date[1]);
    $day   = sprintf("%02d", $date[2]);

    // Use yesterday as default.
    if (!$day || $day == "00") {
      $year  = $defyear;
      $month = $defmonth;
      $day   = $defday;
    }
    $daysmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    if ($day > $daysmonth) {
      $day = $daysmonth;
    }

    return [$year, $month, $day];
  }

  /**
   * Return month as string, given month as number.
   */
  public function monthnum2monthstr($monnum) {

    $monthnames = ["january", "february", "march", "april", "may", "june",
      "july", "august", "september", "october", "november", "december",
    ];

    if ($monnum < 1 || $monnum > 12) {
      return "NA";
    }
    else {
      return $monthnames[$monnum - 1];
    }
  }

  /**
   * Get the time for the product.
   */
  public function getProductTime($tile) {
    // Extract prinfo.
    $prinfo = [];
    $query_from_request = $this->request->getCurrentRequest()->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    $cloud_coverage = $query['cloud'];

    $module_config = $this->config('products_comparison.configuration');
    $rows = (int) $module_config->get('rows');
    $this->getLogger('comparison_module')->debug('Selected cloud coverage: ' . $cloud_coverage);
    $this->getLogger('comparison_module')->debug('Got tilename: ' . $tile);
    $this->getLogger('comparison_module')->debug('Got rows: ' . $rows);
    $query_prd = 'title:S2*' . $tile . '*';

    // Define the fields to be added to the resultSet.
    $fields[] = 'id';
    $fields[] = 'metadata_identifier';
    $fields[] = 'temporal_extent_start_date';
    $fields[] = 'temporal_extent_end_date';
    $fields[] = 'title';
    // $fields[] = 'data_access_url_http';
    // $fields[] = 'data_access_url_odata';
    $fields[] = 'data_access_url_ogc_wms';
    // $fields[] = 'data_access_url_odata';
    // $fields[] = 'data_access_wms_layers';
    // $fields[] = 'platform_ancillary_cloud_coverage';
    $fields[] = 'geographic_extent_rectangle_south';
    $fields[] = 'geographic_extent_rectangle_north';
    $fields[] = 'geographic_extent_rectangle_west';
    $fields[] = 'geographic_extent_rectangle_east';

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $query = $connector->getSelectQuery();
    $query->setFields($fields);
    $query->setQuery($query_prd);
    $query->setStart(0)->setRows($rows);

    $query->addSort('last_metadata_update_datetime', $query::SORT_DESC);
    if ($cloud_coverage !== 'None') {
      $query->createFilterQuery('cloud_coverage')->setQuery('platform_ancillary_cloud_coverage:[0 TO ' . $cloud_coverage . ']');

    }
    $this->getLogger('comparison_module')->debug("Executing query");
    $this->getLogger('comparison_module')->debug($query->getQuery());
    $result = $connector->execute($query);
    $this->getLogger('comparison_module')->debug("Finished executing query, got: " . $result->getNumFound());
    $count = 1;
    foreach ($result as $doc) {
      $fields = $doc->getFields();
      if (preg_match("/OPER/", $fields['title'][0])) {
        $time_string = explode('_', $fields['title'][0], 12)[7];
      }
      else {
        $time_string = explode('_', $fields['title'][0], 7)[6];
      }
      $id = $fields['title'][0];
      if (isset($fields['data_access_url_ogc_wms'])) {
        $address = $fields['data_access_url_ogc_wms'][0];
      }
      else {
        $address = NULL;
      }
      $north = $fields['geographic_extent_rectangle_north'];
      $south = $fields['geographic_extent_rectangle_south'];
      $east = $fields['geographic_extent_rectangle_east'];
      $west = $fields['geographic_extent_rectangle_west'];
      $ayear = substr($time_string, 0, 4);
      $amonth = substr($time_string, 4, 2);
      $aday = substr($time_string, 6, 2);
      $atime = substr($time_string, 8, 7);
      $date = [
        'year' => $ayear,
        'month' => $amonth,
        'day' => $aday,
        'time' => $atime,
      ];
      $latlon = [($south + $north) / 2, ($east + $west) / 2];
      if ($address != NULL) {
        array_push($prinfo, [
          'date' => $date,
          'id' => $id,
          'url' => $address,
          'latlon' => $latlon,
        ]);
        $count = $count + 1;
      }
    }
    rsort($prinfo);
    $response = new AjaxResponse();
    $response->setData($prinfo);
    return $response;
  }

}
