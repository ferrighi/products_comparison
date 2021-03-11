console.log("Start of products_comparison map script:");
(function($, Drupal, drupalSettings) {

    console.log("Attaching products_comparison script to drupal behaviours:");
    /** Attach the metsis map to drupal behaviours function */
    Drupal.behaviors.productsComparison = {
      attach: function(context, drupalSettings) {
        $('#comparisonID', context).each(function() {
          //$('#map-res', context).once('metsisSearchBlock').each(function() {
          /** Start reading drupalSettings sent from the mapblock build */
          console.log('Initializing products_comparison Map...');

          //Reading drupal drupalSettings
          var lat = drupalSettings.products_comparison.lat;
          var lon = drupalSettings.products_comparison.lon;
          var zoomv = drupalSettings.products_comparison.zoomv;
          var prinfo = drupalSettings.products_comparison.prinfo;
          var time_path = drupalSettings.products_comparison.time_path;

          console.log("ZoomLevel: " + zoomv);

          // WGS 84 / UPS North (N,E)
          proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
          ol.proj.proj4.register(proj4);
          var proj32661 = ol.proj.get('EPSG:32661');
          var ex32661 = [-4e+06, -6e+06, 8e+06, 8e+06];
          proj32661.setExtent(ex32661);
          ol.proj.addProjection(proj32661);

          var ext = ex32661;
          var prj = proj32661;

          var selectedId1 = 'None';
          var selectedId2 = 'None';

          //Intialize with no selected tile.
          $('#tileSelect').val('None');
          var selectedTile =  $('#tileSelect').val();
          console.log("Initial selected tile: " + selectedTile);

          //Initialize default layers
          $('#layer1Id').val('true_color_vegetation');
          $('#layer2Id').val('true_color_vegetation');


          //Define default layers for products.
          var prod1Layer = $('#layer1Id').val();
          var prod2Layer = $('#layer2Id').val();
          console.log("Initial layer 1 name: " + prod1Layer);
          console.log("Initial layer 2 name: " + prod2Layer);


          //Set the cloud coverage:
           $('#cloudSelect').val('None');
          var cloudCoverage = $('#cloudSelect').val();
          console.log("Selected cloude coverage: " + cloudCoverage);


          // Define stiles for tiles: red is selected, blue is for mouse moving
          var styleRed = new ol.style.Style({
            stroke: new ol.style.Stroke({
              color: '#f00',
              width: 2
            }),
            fill: new ol.style.Fill({
              color: 'rgba(255,0,0,0.3)'
            }),
          });
          var styleBlue = new ol.style.Style({
            stroke: new ol.style.Stroke({
              color: '#00f',
              width: 2
            }),
          });



          // Base layer WMS
          var baseLayer = new ol.layer.Tile({
            title: 'Base Layer',
            type: 'base',
            source: new ol.source.OSM(),
          });

          // feature layer KML
          var kmlTilelayer = new ol.layer.Vector({
            title: 'Tiles',
            source: new ol.source.Vector({
              url: '/sites/all/files/kml/stripped_tiles_np.kml',
              format: new ol.format.KML({
                extractStyles: false,
                extractAttributes: true
              }),
            })
          });

          var productLayer1 = new ol.layer.Tile({
            title: 'Product 1',
            source: new ol.source.TileWMS({})
          });
          var productLayer2 = new ol.layer.Tile({
            title: 'Product 2',
            source: new ol.source.TileWMS({})
          });

          //var centerLonLat = [lon, lat];
          //var centerTrans = ol.proj.transform(centerLonLat, "EPSG:4326", prj);

          // build up the map
          var map = new ol.Map({
            controls: ol.control.defaults().extend([
              new ol.control.FullScreen()
            ]),
            target: 'map',
            layers: [baseLayer,
              kmlTilelayer,
              productLayer1,
              productLayer2,
            ],
            view: new ol.View({
              zoom: zoomv,
              minZoom: 1,
              maxZoom: 12,
              //center: centerTrans,
              center: ol.proj.transform([15, 71], "EPSG:4326", prj),
              projection: prj,
              extent: ext
            }),
          });
          //Layer switcher
          var layerSwitcher = new ol.control.LayerSwitcher({});
          map.addControl(layerSwitcher);

          //Mouseposition
          var mousePositionControl = new ol.control.MousePosition({
            coordinateFormat: function(co) {
              return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
            },
            projection: 'EPSG:4326',
          });
          map.addControl(mousePositionControl);


          //Blue border when moving over a tile
          map.on('pointermove', function(evt) {
            if(!evt.dragging) {
            map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
              if(feature.get('name') !== null) {
                //console.log("Pointer on feature tile: " + feature.get('name'));
                if (feature.getStyle() !== styleRed){
                   feature.setStyle(null);
                }
              }
            });
            map.forEachFeatureAtPixel(evt.pixel, function(feature) {
                if (feature.getStyle() !== styleRed){
                   feature.setStyle(styleBlue);
                }
            });
          }
          });

  /*
          var selected = null;
          map.on('pointermove', function (e) {
            if (selected !== null) {
              selected.setStyle(undefined);
              selected = null;
            }

            map.forEachFeatureAtPixel(e.pixel, function (f) {
              selected = f;
              //if (selected.getStyle() !== styleRed) {
                selected.setStyle(styleBlue);
                return true;
              //}
              });
*/
          /*  if (selected) {
              status.innerHTML = '&nbsp;Hovering: ' + selected.get('name');
            } else {
              status.innerHTML = '&nbsp;';
            }
            */
          //});


          // the kml source until it is ready.
          /*        map.getLayers().forEach(function(layer, index, array) {
                    if (index >= 1) {
                      let vectorSource = layer.getSource();
                      vectorSource.once('change', function() {
                        if (vectorSource.getState() === 'ready') {
                          map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
                            if ($('#tileSelect').val().slice(1, 6) == feature.get("name")) {
                              feature.setStyle(styleRed);
                              var ext = feature.getGeometry().getExtent();
                              map.getView().fit(ext, map.getSize());
                            }
                          });

                        }

                      });
                    }
                  });
          */
          var productData = null;

          function getProductData(tile) {
            fetch('/services/comparison/date/' + tile + '?cloud=' + cloudCoverage)
              .then((resp) => resp.json())
              .then(function(data) {
                //console.log(data);
                productData = data;
                $('#productOne option').remove();
                $('#productTwo option').remove();
                $('#productOne').append('<option value=None selected="selected">YYYY/MM/DD - THHMMSS</option>');
                $('#productTwo').append('<option value=None selected="selected">YYYY/MM/DD - THHMMSS</option>');
                data.forEach(function(e, i) {
                  $('#productOne').append($('<option></option>').val(e.id).text(e.date.year + '/' + e.date.month + '/' + e.date.day + ' - ' + e.date.time));
                  $('#productTwo').append($('<option></option>').val(e.id).text(e.date.year + '/' + e.date.month + '/' + e.date.day + ' - ' + e.date.time));
                });

              })

              .catch(function(error) {
                console.log(error);
              });
          }


          $('#cloudSelect').on('change', function(){
            cloudCoverage = $(this).val();
            if(selectedTile != 'None') {
              getProductData(selectedTile);
            }
          });


          //Add listener for tile select option
           $('#tileSelect').on('change', function() {
            if($(this).val() !== null) {
            selectedTile = $(this).val();
            console.log("Selected tile: " + selectedTile);
            getProductData(selectedTile);
            map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
              if (selectedTile.slice(1, 6) == feature.get("name")) {
                feature.setStyle(styleRed);
                var ext = feature.getGeometry().getExtent();
                map.getView().fit(ext, map.getSize());
              } else {
                feature.setStyle(undefined);
              }
              if (selectedTile === 'None') {
                map.getView().fit(ex32661, map.getSize());
                map.getView().setCenter(ol.proj.transform([15, 71], "EPSG:4326", prj));
                map.getView().setZoom(zoomv);
              }
            });
          }
          });

          //Red color when selecting a tile.
      /*    map.on('click', function(evt) {
            kmlTilelayer.getFeatures(evt.pixel).then(function (features) {
              var feature = features.length ? features[0] : undefined;

              if (features.length) {
                feature.setStyle(styleRed);
                let selectedTile = feature.get('name');
                console.log(selectedTile);
                $('#tileSelect').val('T' + selectedTile).change();
                //getProductData('T' + selectedTile);
                map.getLayers().getArray()[1].getSource().forEachFeature(function(feature) {
                  if (selectedTile.slice(1, 6) == feature.get("name")) {
                    feature.setStyle(styleRed);
                    var ext = feature.getGeometry().getExtent();
                    map.getView().fit(ext, map.getSize());
                  } else {
                    feature.setStyle(undefined);
                  }
                });

              }
            });
          });
*/
              //Red color when selecting a tile.
              var tileClick = 'None';
              map.on('click', function(evt) {
                tileClick = map.forEachFeatureAtPixel(evt.pixel, function(feature) {
                feature.setStyle(styleRed);
                console.log("Clicked tile : " + feature.get('name'));
                return feature.get("name");
                });
              //Select the html and assign value to it depending on the selected tile.
              if(tileClick !== 'None' ) {
                selectedTile = 'T' + tileClick;
                $('#tileSelect').val(selectedTile).change();
              }
              });
          /*    if (feature !== highlight) {
                if (highlight) {
                  featureOverlay.getSource().removeFeature(highlight);
                }
                if (feature) {
                  featureOverlay.getSource().addFeature(feature);
                }
                highlight = feature;
              }
*/
            /*
            feature.setStyle(styleRed);
            var tileSelect = $('#tileSelect');
            tileSelect.val('T' + tile);
            getProductData('T' + feature.get("name"));
            var ext = feature.getGeometry().getExtent();
            map.getView().fit(ext, map.getSize());
            return feature.get("name");
            */
          //Select the html and assign value to it depending on the selected tile.
          //var tfm = document.getElementById('selectform');
          //tfm.elements["tile"].value = 'T' + tile;
          //tfm.elements["tile"].onchange();


        //});
      //});

        $('#productOne').on('change', function() {
          var id = $(this).val();
          console.log('Chosen prod 1 id: ' + id);
          selectedId1 = id;
          productData.forEach(function(e, i) {
            if (e.id === id) {
              let wmsUrl = e.url;
              wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
              wmsUrl = wmsUrl.split("?")[0];
              productLayer1.setSource(
                new ol.source.TileWMS({
                  url: wmsUrl,
                  params: {
                    'LAYERS': prod1Layer,
                    'TRANSPARENT': 'true',
                    'FORMAT': 'image/png',
                    'VERSION': '1.3.0',
                  }
                }))
            }
          });
          if(selectedId1 !== 'None' && selectedId2 !== 'None') {
            $('#compInfo').html('<strong>You are comparing Sentinel-2 products:</strong><br>' + selectedId1 + '<br>' + selectedId2);
          }
        });

        $('#productTwo').on('change', function() {
          var id = $(this).val();
          console.log('Chosen prod 2 id: ' + id);
          selectedId2 = id
          productData.forEach(function(e, i) {
            if (e.id === id) {
              let wmsUrl = e.url;
              wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
              wmsUrl = wmsUrl.split("?")[0];
              productLayer2.setSource(
                new ol.source.TileWMS({
                  url: wmsUrl,
                  params: {
                    'LAYERS': prod2Layer,
                    'TRANSPARENT': 'true',
                    'FORMAT': 'image/png',
                    'VERSION': '1.3.0',
                  }
                }))
            }
          });
          if(selectedId1 !== 'None' && selectedId2 !== 'None') {
            $('#compInfo').html('<strong>You are comparing Sentinel-2 products:</strong><br>' + selectedId1 + '<br>' + selectedId2);
          }
        });
        //var centerLonLat = [lon, lat];
        //var centerTrans = ol.proj.transform(centerLonLat, "EPSG:4326",  prj);
        var swipe = document.getElementById('swipe');
/*
        productLayer2.on('precompose', function(event) {
          var ctx = event.context;
          var width = ctx.canvas.width * (swipe.value / 100);

          ctx.save();
          ctx.beginPath();
          ctx.rect(width, 0, ctx.canvas.width - width, ctx.canvas.height);
          ctx.clip();
        });

        productLayer2.on('postcompose', function(event) {
          var ctx = event.context;
          ctx.restore();
        });
*/

        //Define change layers on products.
        $('#layer1Id').on('change', function() {
          prod1Layer = $(this).val();
          console.log("Chosen Product 1 layer: " + prod1Layer);
          productLayer1.getSource().updateParams({
            'LAYERS': prod1Layer
          });
        });

        $('#layer2Id').on('change', function() {
          prod2Layer = $(this).val();
          console.log("Chosen Product 2 layer: " + prod2Layer);
          productLayer2.getSource().updateParams({
            'LAYERS': prod2Layer
          });
        });




        swipe.addEventListener('input', function() {
          let opValue = swipe.value / 100;
          productLayer1.setOpacity(1);
          productLayer2.setOpacity(opValue);


        });

        console.log("End of products_comparison script");
      });
  },
};

})(jQuery, Drupal, drupalSettings);
