// WGS 84 / UPS North (N,E)
proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
var proj32661 = ol.proj.get('EPSG:32661');
var ex32661 = [-4e+06,-6e+06,8e+06,8e+06];
proj32661.setExtent(ex32661);
ol.proj.addProjection(proj32661);

var ext = ex32661;
var prj = proj32661;

var tromsoLonLat = [10, 68];
var tromsoTrans = ol.proj.transform(tromsoLonLat, "EPSG:4326",  prj);

var layer = {};

// Base layer WMS
layer['base']  = new ol.layer.Tile({
   type: 'base',
   source: new ol.source.TileWMS({ 
       url: 'http://public-wms.met.no/backgroundmaps/northpole.map',
       params: {'LAYERS': 'world', 'TRANSPARENT':'false', 'VERSION':'1.1.1','FORMAT':'image/png', 'SRS':prj}
   })
});

// Border layer WMS
layer['border']  = new ol.layer.Tile({
   source: new ol.source.TileWMS({ 
       url: 'http://public-wms.met.no/backgroundmaps/northpole.map',
       params: {'LAYERS': 'borders', 'TRANSPARENT':'true', 'VERSION':'1.1.1','FORMAT':'image/png', 'SRS':prj}
   })
});

layer['l1']  = new ol.layer.Tile({
       title: 'First',
   source: new ol.source.TileWMS({ 
       url: prod_id1,
       params: {'LAYERS': 'true_color_vegetation', 'TRANSPARENT':'true', 'FORMAT':'image/png', 'CRS':'EPSG:32661', 'VERSION':'1.3.0', 'SERVICE':'WMS','REQUEST':'GetMap','TILE':'true','WIDTH':'256','HEIGHT':'256'}
   })
});

layer['l2']  = new ol.layer.Tile({
       title: 'Second',
   source: new ol.source.TileWMS({ 
       url: prod_id2,
       params: {'LAYERS': 'true_color_vegetation', 'TRANSPARENT':'true', 'FORMAT':'image/png', 'CRS':'EPSG:32661', 'VERSION':'1.3.0', 'SERVICE':'WMS','REQUEST':'GetMap','TILE':'true','WIDTH':'256','HEIGHT':'256'}
   })
});

// build up the map
var map = new ol.Map({
   target: 'map',
   layers: [ layer['base'], 
             layer['l1'],
             layer['l2'],
             layer['border']
           ],
   view: new ol.View({
                 zoom: 3, 
                 minZoom: 3,
                 center: tromsoTrans,
                 projection: prj,
                 extent: ext
   })
});
//var layerSwitcher = new ol.control.LayerSwitcher({});
//map.addControl(layerSwitcher);
//layerSwitcher.showPanel();

var swipe = document.getElementById('swipe');

layer['l2'].on('precompose', function(event) {
        var ctx = event.context;
        //var width = ctx.canvas.width * (swipe.value / 100);
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
