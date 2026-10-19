import { h, render } from 'preact';
import { useEffect, useState } from 'preact/hooks';
import 'ol/ol.css';
import './style.css';
import Map from 'ol/Map.js';
import View from 'ol/View.js';
import TileLayer from 'ol/layer/Tile.js';
import LayerGroup from 'ol/layer/Group.js';
import Collection from 'ol/Collection.js';
import VectorLayer from 'ol/layer/Vector.js';
import { OSM, TileWMS, Vector as VectorSource } from 'ol/source';
import { Style, Stroke, Fill } from 'ol/style.js';
import { transform } from 'ol/proj.js';
import { getRenderPixel } from 'ol/render.js';
import { FullScreen, MousePosition, defaults as defaultControls } from 'ol/control';
import KML from 'ol/format/KML.js';
import { get as getProjection } from 'ol/proj.js';
import proj4 from 'proj4';
import { register } from 'ol/proj/proj4.js';
import { toStringHDMS } from 'ol/coordinate.js';

const DEFAULT_LAYER = 'true_color_vegetation';
const LAYERS = [
  ['true_color_vegetation', 'True Color Vegetation'],
  ['false_color_vegetation', 'False Color Vegetation'],
  ['false_color_glacier', 'False Color Glacier'],
];

function productLabel(product) {
  return product ? `${product.date.year}/${product.date.month}/${product.date.day} - ${product.date.time}` : 'YYYY/MM/DD - THHMMSS';
}

function requestProducts(url, signal) {
  return fetch(url, { headers: { Accept: 'application/json' }, signal })
    .then((response) => {
      if (!response.ok) throw new Error(`Product request failed (${response.status})`);
      return response.json();
    })
    .then((products) => products);
}

function ProductControls({ settings, onProductChange, onTileChange }) {
  const [tile, setTile] = useState('None');
  const [cloud, setCloud] = useState('None');
  const [products, setProducts] = useState([]);
  const [selected, setSelected] = useState(['None', 'None']);
  const [layers, setLayers] = useState([DEFAULT_LAYER, DEFAULT_LAYER]);
  const [plotted, setPlotted] = useState([false, false]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const endpoint = settings.time_path || '/services/comparison/date/{tile}';
  const labels = settings.labels || {};

  useEffect(() => {
    if (tile === 'None') {
      setProducts([]);
      setSelected(['None', 'None']);
      return undefined;
    }
    const controller = new AbortController();
    setLoading(true);
    setError('');
    const url = endpoint.replace('{tile}', encodeURIComponent(tile)) + `?cloud=${encodeURIComponent(cloud)}`;
    requestProducts(url, controller.signal)
      .then((data) => {
        setProducts(Array.isArray(data) ? data : []);
        setSelected(['None', 'None']);
        setPlotted([false, false]);
        onProductChange(0, null, layers[0], false);
        onProductChange(1, null, layers[1], false);
      })
      .catch((requestError) => {
        if (requestError.name !== 'AbortError') setError(labels.error || 'Unable to load products.');
      })
      .finally(() => setLoading(false));
    return () => controller.abort();
  }, [tile, cloud]);

  const changeProduct = (index, value) => {
    const next = [...selected];
    next[index] = value;
    setSelected(next);
    setPlotted((current) => {
      const nextPlotted = [...current];
      nextPlotted[index] = false;
      return nextPlotted;
    });
    onProductChange(index, products.find((product) => product.id === value) || null, layers[index], false);
  };
  const changeLayer = (index, value) => {
    const next = [...layers];
    next[index] = value;
    setLayers(next);
    const product = products.find((item) => item.id === selected[index]);
    if (product && plotted[index]) onProductChange(index, product, value, true);
  };
  const togglePlot = (index) => {
    const product = products.find((item) => item.id === selected[index]);
    if (!product) return;
    const shouldPlot = !plotted[index];
    onProductChange(index, product, layers[index], shouldPlot);
    setPlotted((current) => {
      const next = [...current];
      next[index] = shouldPlot;
      return next;
    });
  };
  const productOptions = (index) => products.map((product) => h('option', {
    value: product.id,
    disabled: selected[1 - index] === product.id,
  }, productLabel(product)));

  return h('div', { class: 'comparison-controls', 'aria-busy': loading },
    h('div', { class: 'shared-controls' },
      h('label', null, labels.tile || 'Tile',
        h('select', { id: 'tileSelect', name: 'tile', class: 'drop-selection', value: tile, onChange: (event) => { setTile(event.currentTarget.value); onTileChange(event.currentTarget.value); } },
          h('option', { value: 'None' }, labels.none || 'None'),
          (settings.tiles || []).map((item) => h('option', { value: item }, item)))),
      h('label', null, labels.cloud || 'Cloud coverage %',
        h('select', { id: 'cloudSelect', name: 'cloud', class: 'drop-selection', value: cloud, onChange: (event) => setCloud(event.currentTarget.value) },
          h('option', { value: 'None' }, labels.cloudPlaceholder || 'Cloud coverage %'),
          ...[10, 20, 30, 40, 50, 60, 70, 80, 90].map((value) => h('option', { value: value / 100 }, `<${value}%`))))),
    h('div', { class: 'product-controls' },
      [0, 1].map((index) => h('section', { class: 'product-card', 'aria-labelledby': `product-title-${index}` },
        h('h3', { id: `product-title-${index}` }, labels.productTitle?.[index] || `Product ${index + 1}`),
        h('label', null, labels.product || 'Date - Time',
          h('select', { id: index ? 'productTwo' : 'productOne', name: index ? 'pr2' : 'pr1', class: 'drop-selection', value: selected[index], onChange: (event) => changeProduct(index, event.currentTarget.value) },
            h('option', { value: 'None' }, labels.datePlaceholder || 'YYYY/MM/DD - THHMMSS'), productOptions(index))),
        h('label', null, labels.composite || 'Composite',
          h('select', { id: index ? 'layer2Id' : 'layer1Id', name: index ? 'layer2' : 'layer1', class: 'drop-selection', value: layers[index], onChange: (event) => changeLayer(index, event.currentTarget.value) },
            LAYERS.map(([value, text]) => h('option', { value }, text)))),
        h('button', { type: 'button', class: 'plot-button', disabled: selected[index] === 'None', onClick: () => togglePlot(index) },
          plotted[index] ? (labels.hide || 'Hide') : (labels.plot || 'Plot'))))),
    loading && h('span', { class: 'comparison-status', role: 'status' }, labels.loading || 'Loading…'),
    error && h('span', { class: 'comparison-error', role: 'alert' }, error),
    selected[0] !== 'None' && selected[1] !== 'None' && h('div', { id: 'compInfo', class: 'comparison-info' }, h('strong', null, labels.info || 'You are comparing Sentinel-2 products:'), h('br'), selected[0], h('br'), selected[1])
  );
}

function createMap(settings, mapElement, controls) {
  proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
  register(proj4);
  const projection = getProjection('EPSG:32661');
  const extent = [-4e6, -6e6, 8e6, 8e6];
  projection.setExtent(extent);
  const red = new Style({ stroke: new Stroke({ color: '#f00', width: 2 }), fill: new Fill({ color: 'rgba(255,0,0,0.3)' }) });
  const blue = new Style({ stroke: new Stroke({ color: '#00f', width: 2 }) });
  const kmlLayer = new VectorLayer({ title: 'Tiles', source: new VectorSource({ url: `${settings.kml_path || '/modules/custom/products_comparison/js/comparison-app/dist/kml/stripped_tiles_np.kml'}`, format: new KML({ extractStyles: false, extractAttributes: true }) }) });
  const productLayers = [
    new LayerGroup({ title: 'Product 1', layers: [], visible: false }),
    new LayerGroup({ title: 'Product 2', layers: [], visible: false }),
  ];
  const map = new Map({
    controls: defaultControls().extend([new FullScreen({ source: mapElement })]), target: mapElement,
    layers: [new TileLayer({ title: 'Base Layer', type: 'base', source: new OSM() }), kmlLayer, ...productLayers],
    view: new View({ zoom: settings.zoomv, minZoom: 1, maxZoom: 12, center: transform([settings.lon, settings.lat], 'EPSG:4326', projection), projection, extent }),
  });
  const swipe = mapElement.querySelector('#swipe');
  const clipLayer = (layer) => {
    layer.on('prerender', (event) => {
      const context = event.context;
      const mapSize = map.getSize();
      if (!mapSize || !swipe) return;
      const width = mapSize[0] * (Number(swipe.value) / 100);
      const topLeft = getRenderPixel(event, [width, 0]);
      const topRight = getRenderPixel(event, [mapSize[0], 0]);
      const bottomRight = getRenderPixel(event, [mapSize[0], mapSize[1]]);
      const bottomLeft = getRenderPixel(event, [width, mapSize[1]]);

      context.save();
      context.beginPath();
      context.moveTo(topLeft[0], topLeft[1]);
      context.lineTo(topRight[0], topRight[1]);
      context.lineTo(bottomRight[0], bottomRight[1]);
      context.lineTo(bottomLeft[0], bottomLeft[1]);
      context.closePath();
      context.clip();
    });
    layer.on('postrender', (event) => event.context.restore());
  };
  swipe?.addEventListener('input', () => map.render());
  map.addControl(new MousePosition({
    className: 'comparison-mouse-position',
    coordinateFormat: (coordinate) => toStringHDMS(coordinate, 2),
    projection: 'EPSG:4326',
  }));
  const overlay = new VectorLayer({ source: new VectorSource(), map, style: blue });
  let highlight;
  map.on('pointermove', (event) => {
    if (event.dragging) return;
    kmlLayer.getFeatures(map.getEventPixel(event.originalEvent)).then((features) => {
      if (features[0] !== highlight) {
        if (highlight) overlay.getSource().removeFeature(highlight);
        highlight = features[0];
        if (highlight) overlay.getSource().addFeature(highlight);
      }
    });
  });
  const selectTile = (value) => {
    kmlLayer.getSource().forEachFeature((feature) => {
      if (value !== 'None' && value.slice(1) === feature.get('name')) {
        feature.setStyle(red);
        map.getView().fit(feature.getGeometry().getExtent(), { size: map.getSize() });
      } else feature.setStyle(null);
    });
    if (value === 'None') {
      map.getView().setCenter(transform([settings.lon, settings.lat], 'EPSG:4326', projection));
      map.getView().setZoom(settings.zoomv);
    }
  };
  controls.onTileChange = selectTile;
  map.on('click', (event) => {
    const feature = map.forEachFeatureAtPixel(event.pixel, (item) => item);
    if (feature && feature.get('name')) {
      controls.setTile(`T${feature.get('name')}`);
    }
  });
  return { map, productLayers, clipLayer };
}

function attachMap(Drupal, drupalSettings) {
  Drupal.behaviors.productsComparison = {
    attach(context) {
      context.querySelectorAll('[data-product-comparison]:not([data-products-comparison-processed])').forEach((mapElement) => {
        const controlsRoot = document.querySelector('[data-product-controls]');
        const settings = { ...(drupalSettings.products_comparison || {}) };
        if (!settings.tiles && controlsRoot?.dataset.tiles) {
          try { settings.tiles = JSON.parse(controlsRoot.dataset.tiles); } catch (error) { settings.tiles = []; }
        }
        const controls = { setTile: (value) => controlsRoot?.dispatchEvent(new CustomEvent('products-comparison:set-tile', { detail: value })) };
        const mapState = createMap(settings, mapElement, controls);
        const updateLayer = (index, product, layer, visible) => {
          if (!product) {
              mapState.productLayers[index].setLayers(new Collection());
            mapState.productLayers[index].setVisible(false);
            return;
          }
          const resources = Array.isArray(product?.resources) && product.resources.length
            ? product.resources
            : product?.url ? [{ url: product.url }] : [];
          mapState.productLayers[index].setLayers(new Collection(resources.map((resource) => {
            const tileLayer = new TileLayer({
              source: new TileWMS({
                url: resource.url.split('?')[0].replace(/(^\w+:|^)\/\//, '//'),
                params: { LAYERS: layer, TRANSPARENT: 'true', FORMAT: 'image/png', VERSION: '1.3.0' },
              }),
            });
            if (index === 1) mapState.clipLayer(tileLayer);
            return tileLayer;
          })));
          mapState.productLayers[index].setVisible(visible);
        };
        render(h(ProductControls, { settings, onProductChange: updateLayer, onTileChange: controls.onTileChange || (() => {}) }), controlsRoot);
        controlsRoot?.addEventListener('products-comparison:set-tile', (event) => {
          const select = controlsRoot.querySelector('#tileSelect');
          if (select) { select.value = event.detail; select.dispatchEvent(new Event('change', { bubbles: true })); }
          mapState.map.getTargetElement() && mapState.map.updateSize();
        });
        mapElement.setAttribute('data-products-comparison-processed', 'true');
      });
    },
  };
}

attachMap(Drupal, drupalSettings);
