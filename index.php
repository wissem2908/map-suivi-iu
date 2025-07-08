<!DOCTYPE html>
<html>
<head>
  <title>GeoJSON Layers Suivi IU</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      /* background-color: #222; */
    }
    #map {
      height: 50vh;
    }
    .leaflet-popup-content-wrapper {
      background-color: #2c2c2c;
      color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }
    .leaflet-popup-tip {
      background-color:rgb(83, 13, 13);
    }
  </style>
</head>
<body>

<div id="map"></div>

<script>
const map = L.map('map', {
  attributionControl: false
});

map.setView([30, 5], 4);

L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; CartoDB',
  subdomains: 'abcd',
  maxZoom: 18
}).addTo(map);


  let layer1, layer2, layer3, layer4;
  let layersLoaded = 0;

  // Format date (ms timestamp → DD/MM/YYYY)
  function formatDate(timestamp) {
    if (!timestamp) return "N/A";
    const date = new Date(timestamp);
    return date.toLocaleDateString('fr-FR');
  }

  function checkAllLoaded() {
    layersLoaded++;
    if (layersLoaded === 4) {
      updateLayers();
      map.on('zoomend', updateLayers);
    }
  }

  // Wilaya - Red
  $.getJSON('geojson/wilaya.geojson', function(data) {
    layer1 = L.geoJSON(data, {
      style: {
        color: "#e74c3c", // red
        weight: 2,
        fillOpacity: 0.1
      }
    });
    checkAllLoaded();
  });

  // Commune - Blue
  $.getJSON('geojson/Commune.geojson', function(data) {
    layer2 = L.geoJSON(data, {
      style: {
        color: "#3498db", // blue
        weight: 2,
        fillOpacity: 0.1
      }
    });
    checkAllLoaded();
  });

  // PDAU - Green + modern popup
  $.getJSON('geojson/Pdau.geojson', function(data) {
    layer3 = L.geoJSON(data, {
      style: {
        color: "#2ecc71", // green
        weight: 2,
        fillOpacity: 0.1
      },
      onEachFeature: function (feature, layer) {
        const p = feature.properties;
        const content = `
          <div style="
            font-family: 'Segoe UI', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            background-color: #2c2c2c;
            padding: 10px;
            border-radius: 8px;
            color: #fff;
          ">
            <h4 style="margin:0 0 8px 0; font-size:16px; color:#2ecc71;">📄 PDAU</h4>
            <strong>Commune:</strong> ${p.commune_1}<br>
            <strong>Bureau:</strong> ${p.bureau_det}<br>
            <strong>Type étude:</strong> ${p.etude}<br>
            <strong>État:</strong> ${p.etat_d_ava}<br>
            <strong>Créé par:</strong> ${p.created_us}<br>
            <strong>Date création:</strong> ${formatDate(p.created_da)}<br>
            <strong>Dernière édition:</strong> ${p.last_edite}<br>
            <strong>Surface:</strong> ${p.st_area_sh}
          </div>
        `;
        layer.bindPopup(content);
      }
    });
    checkAllLoaded();
  });

  // POS - Orange + modern popup
  $.getJSON('geojson/Pos.geojson', function(data) {
    layer4 = L.geoJSON(data, {
      style: {
        color: "#f39c12", // orange
        weight: 2,
        fillOpacity: 0.1
      },
      onEachFeature: function (feature, layer) {
        const p = feature.properties;
        const content = `
          <div style="
            font-family: 'Segoe UI', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            background-color: #2c2c2c;
            padding: 10px;
            border-radius: 8px;
            color: #fff;
          ">
            <h4 style="margin:0 0 8px 0; font-size:16px; color:#f39c12;">📌 ${p.n_pos}</h4>
            <strong>Nom:</strong> ${p.nom}<br>
            <strong>Commune:</strong> ${p.commune}<br>
            <strong>Bureau:</strong> ${p.bureauetud}<br>
            <strong>Surface:</strong> ${p.sup} ha<br>
            <strong>COS moyen:</strong> ${p.cos_moy}<br>
            <strong>CES moyen:</strong> ${p.ces_moy}<br>
            <strong>Règlement:</strong> ${p.réglement}<br>
            <strong>Observations:</strong> ${p.observatio}<br>
            <strong>Créé par:</strong> ${p.created_us}<br>
            <strong>Date création:</strong> ${formatDate(p.created_da)}
          </div>
        `;
        layer.bindPopup(content);
      }
    });
    checkAllLoaded();
  });

  // Display correct layer per zoom level
  function updateLayers() {
    const zoom = map.getZoom();
    [layer1, layer2, layer3, layer4].forEach(l => {
      if (l && map.hasLayer(l)) map.removeLayer(l);
    });

    if (zoom < 6) {
      if (layer1) map.addLayer(layer1);
    } else if (zoom < 8) {
      if (layer2) map.addLayer(layer2);
    } else if (zoom < 10) {
      if (layer3) map.addLayer(layer3);
    } else {
      if (layer4) map.addLayer(layer4);
    }
  }
</script>

</body>
</html>
