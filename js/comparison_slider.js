// WGS 84 / UPS North (N,E)
proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
var proj32661 = ol.proj.get('EPSG:32661');
var ex32661 = [-4e+06,-6e+06,8e+06,8e+06];
proj32661.setExtent(ex32661);
ol.proj.addProjection(proj32661);

var ext = ex32661;
var prj = proj32661;


// Define stiles for tiles: red is selected, blue is for mouse moving
var styleRed = new ol.style.Style({
        stroke: new ol.style.Stroke({
          color: '#f00',
          width: 2
        }),
        fill: new ol.style.Fill({
          color: 'rgba(255,0,0,0.3)'
        })
      })
var styleBlue = new ol.style.Style({
        stroke: new ol.style.Stroke({
          color: '#00f',
          width: 2
        }),
      })


var layer = {};

// Base layer WMS
layer['base']  = new ol.layer.Tile({
   type: 'base',
   source: new ol.source.OSM()
//   source: new ol.source.TileWMS({ 
//       url: 'http://public-wms.met.no/backgroundmaps/northpole.map',
//       params: {'LAYERS': 'world', 'TRANSPARENT':'false', 'VERSION':'1.1.1','FORMAT':'image/png', 'SRS':prj}
//   })
});

// Border layer WMS
layer['border']  = new ol.layer.Tile({
   source: new ol.source.TileWMS({ 
       url: 'http://public-wms.met.no/backgroundmaps/northpole.map',
       params: {'LAYERS': 'borders', 'TRANSPARENT':'true', 'VERSION':'1.1.1','FORMAT':'image/png', 'SRS':prj}
   })
});

// feature layer KML 
layer['kml_tiles'] = new ol.layer.Vector({
   title: 'tiles',
   source: new ol.source.Vector({
       url: '/sites/'+site_name+'/files/stripped_tiles_np.kml',
       format: new ol.format.KML({extractStyles: false, extractAttributes: true}),
   })
})

layer['l1']  = new ol.layer.Tile({
       title: id1,
       source: new ol.source.TileWMS({ 
       url: prod_id1,
       params: {'LAYERS': ly1, 'TRANSPARENT':'true', 'FORMAT':'image/png', 'CRS':'EPSG:32661', 'VERSION':'1.3.0', 'SERVICE':'WMS','REQUEST':'GetMap','TILE':'true','WIDTH':'256','HEIGHT':'256'}
   })
});

layer['l2']  = new ol.layer.Tile({
       title: id2,
       source: new ol.source.TileWMS({ 
       url: prod_id2,
       params: {'LAYERS': ly2, 'TRANSPARENT':'true', 'FORMAT':'image/png', 'CRS':'EPSG:32661', 'VERSION':'1.3.0', 'SERVICE':'WMS','REQUEST':'GetMap','TILE':'true','WIDTH':'256','HEIGHT':'256'}
   })
});

var centerLonLat = [lon, lat];
var centerTrans = ol.proj.transform(centerLonLat, "EPSG:4326",  prj);

// build up the map
var map = new ol.Map({
   controls: ol.control.defaults().extend([
      new ol.control.FullScreen()
   ]),
   target: 'map',
   layers: [ layer['base'], 
             layer['kml_tiles'],
             layer['l1'],
             layer['l2'],
             layer['border']
           ],
   view: new ol.View({
                 zoom: zoomv, 
                 minZoom: 3,
                 center: centerTrans,
                 projection: prj,
                 extent: ext
   })
});
//Layer switcher
var layerSwitcher = new ol.control.LayerSwitcher({});
map.addControl(layerSwitcher);

//Mouseposition
var mousePositionControl = new ol.control.MousePosition({
   coordinateFormat : function(co) {
      return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
   },
   projection : 'EPSG:4326', 
});
map.addControl(mousePositionControl);


//Blue border when moving over a tile
map.on('pointermove', function(evt) {
  map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
      if (feature.getStyle() !== styleRed){
         feature.setStyle(null);
      }
  });
  map.forEachFeatureAtPixel(evt.pixel, function(feature) {
      if (feature.getStyle() !== styleRed){
         feature.setStyle(styleBlue);
      }
  });
});


var listenerKey = {};
function listenerAllLayers() {
   if (layer["kml_tiles"].getSource().getRevision() >= 1){
    //if all layers are ready then stop listeing for changes
    layer["kml_tiles"].getSource().unByKey(listenerKey["kml_tiles"]);
    // do something with the source
    map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
      if (tl.slice(1,6) == feature.get("name") ){
        feature.setStyle(styleRed);
        var ext = feature.getGeometry().getExtent();
        map.getView().fit(ext,map.getSize());
      }
    });
  }
}

// build elements of listenerKey for kml layer
listenerKey["kml_tiles"] = layer["kml_tiles"].getSource().on('change', listenerAllLayers);


//Red color when selecting a tile. 
map.on('click', function(evt) {
  tile = map.forEachFeatureAtPixel(evt.pixel, function(feature) {
  feature.setStyle(styleRed);
  return feature.get("name");
  });
//Select the html and assign value to it depending on the selected tile.
  var tfm = document.getElementById('selectform');
  tfm.elements["tile"].value = 'T'+tile;
  tfm.elements["tile"].onchange();
});


var swipe = document.getElementById('swipe');

layer['l2'].on('precompose', function(event) {
        var ctx = event.context;
        var width = ctx.canvas.width * (swipe.value / 100);

        ctx.save();
        ctx.beginPath();
        ctx.rect(width, 0, ctx.canvas.width - width, ctx.canvas.height);
        ctx.clip();
      });

layer['l2'].on('postcompose', function(event) {
  var ctx = event.context;
  ctx.restore();
});

swipe.addEventListener('input', function() {
  map.render();
}, false);

