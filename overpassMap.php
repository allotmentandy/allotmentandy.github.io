<!DOCTYPE html>
<html>
  <head>
    <title>Example: Overpass-API with Leaflet.js</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
      body {
        padding: 0;
        margin: 0;
      }
      html, body, #map {
        height: 100%;
        width: 100%;
      }
      #overpass-api-controls {
        padding: 10px;
        background-color: rgb(255, 255, 255);
      }
      #overpass-api-controls a {
        display: inline;
      }
    </style>
  </head>
  <body>
  
<h2>Overpass Demo</h2>
eg: landuse=allotments 
    <div id="map">
      <div class="leaflet-control-container">
        <div class="leaflet-top leaflet-right">
          <div id="overpass-api-controls" class="leaflet-bar leaflet-control">
            <input id="query-textfield" value="leisure=playground" size="50">
            <input id="query-button" type="button" value="Search">
          </div>
        </div>
      </div>
    </div>
 
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://unpkg.com/osmtogeojson@2.2.12/osmtogeojson.js"></script>

    <script>
      var map = L.map('map').setView([51.487, -0.267], 14);
      L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      function buildOverpassApiUrl(map, overpassQuery) {
        var bounds = map.getBounds().getSouth() + ',' + map.getBounds().getWest() + ',' + map.getBounds().getNorth() + ',' + map.getBounds().getEast();
        var nodeQuery = 'node[' + overpassQuery + '](' + bounds + ');';
        var wayQuery = 'way[' + overpassQuery + '](' + bounds + ');';
        var relationQuery = 'relation[' + overpassQuery + '](' + bounds + ');';
        var query = '?data=[out:json][timeout:15];(' + nodeQuery + wayQuery + relationQuery + ');out body geom;';
        var baseUrl = 'http://overpass-api.de/api/interpreter';
        var resultUrl = baseUrl + query;
        return resultUrl;
      }

      $("#query-button").click(function () {
        var queryTextfieldValue = $("#query-textfield").val();
        var overpassApiUrl = buildOverpassApiUrl(map, queryTextfieldValue);
        
        $.get(overpassApiUrl, function (osmDataAsJson) {
          var resultAsGeojson = osmtogeojson(osmDataAsJson);
          var resultLayer = L.geoJson(resultAsGeojson, {
            style: function (feature) {
              return {color: "#ff0000"};
            },
            filter: function (feature, layer) {
              var isPolygon = (feature.geometry) && (feature.geometry.type !== undefined) && (feature.geometry.type === "Polygon");
              if (isPolygon) {
                feature.geometry.type = "Point";
                var polygonCenter = L.latLngBounds(feature.geometry.coordinates[0]).getCenter();
                feature.geometry.coordinates = [ polygonCenter.lat, polygonCenter.lng ];
              }
              return true;
            },
            onEachFeature: function (feature, layer) {
              var popupContent = "";
              popupContent = popupContent + "<dt>@id</dt><dd>" + feature.properties.type + "/" + feature.properties.id + "</dd>";
              var keys = Object.keys(feature.properties.tags);
              keys.forEach(function (key) {
                popupContent = popupContent + "<dt>" + key + "</dt><dd>" + feature.properties.tags[key] + "</dd>";
              });
              popupContent = popupContent + "</dl>"
              layer.bindPopup(popupContent);
            }
          }).addTo(map);
        });
      });

    </script>
  </body>
</html>