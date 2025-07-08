<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Urban Planning Studies Viewer (PDAU & POS)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #222;
    }
    #map {
      height: 100vh;
    }

    /* Layer Buttons */
    #layer-buttons {
      position: absolute;
      top: 15px;
      left: 15px;
      z-index: 1000;
      background: rgba(0, 0, 0, 0.6);
      padding: 8px;
      border-radius: 8px;
      display: flex;
      gap: 6px;
    }

    #layer-buttons button {
      background: #333;
      color: #ffa500;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }

    #layer-buttons button:hover {
      background: #555;
    }

    #layer-buttons button.active {
      background: #ffa500;
      color: #111;
    }

    /* Popup styling */
    .leaflet-popup-content-wrapper {
      background-color: #2c2c2c;
      color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }
    .leaflet-popup-tip {
      background-color: #2c2c2c;
    }
  </style>
</head>
<body>

<!-- Buttons to toggle layers -->
<div id="layer-buttons">
  <button data-layer="wilaya">Wilaya</button>
  <button data-layer="commune">Commune</button>
  <button data-layer="pdau">PDAU</button>
  <button data-layer="pos">POS</button>
</div>

<!-- Map Container -->
<div id="map"></div>

<script>
  const map = L.map('map', {
    center: [30, 5],
    zoom: 5,
    attributionControl: false,
     zoomControl: false // disables the zoom buttons
  });


L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; CartoDB',
  subdomains: 'abcd',
  maxZoom: 18
}).addTo(map);
  // Format date helper
  function formatDate(timestamp) {
    if (!timestamp) return "N/A";
    const date = new Date(timestamp);
    return date.toLocaleDateString('fr-FR');
  }

  let layer1, layer2, layer3, layer4;
  let activeLayer = null;

  // Load GeoJSON files
  $.getJSON('geojson/wilaya.geojson', function(data) {
    layer1 = L.geoJSON(data, {
      style: { color: "#e74c3c", weight: 2, fillOpacity: 0.1 }
    });
  });

  $.getJSON('geojson/Commune.geojson', function(data) {
    layer2 = L.geoJSON(data, {
      style: { color: "#3498db", weight: 2, fillOpacity: 0.1 }
    });
  });

  $.getJSON('geojson/Pdau.geojson', function(data) {
    layer3 = L.geoJSON(data, {
      style: { color: "#2ecc71", weight: 2, fillOpacity: 0.1 },
      onEachFeature: function (feature, layer) {
        const p = feature.properties;
        const content = `
          <div style="font-size: 13px; line-height: 1.4; color: #fff;">
            <h4 style="margin:0 0 8px; color:#2ecc71;">📄 PDAU</h4>
            <strong>Commune:</strong> ${p.commune_1}<br>
            <strong>Bureau:</strong> ${p.bureau_det}<br>
            <strong>Type étude:</strong> ${p.etude}<br>
            <strong>État:</strong> ${p.etat_d_ava}<br>
            <strong>Créé par:</strong> ${p.created_us}<br>
            <strong>Date création:</strong> ${formatDate(p.created_da)}<br>
            <strong>Dernière édition:</strong> ${p.last_edite}<br>
            <strong>Surface:</strong> ${p.st_area_sh}
          </div>`;
        layer.bindPopup(content);
      }
    });
  });

  $.getJSON('geojson/Pos.geojson', function(data) {
    layer4 = L.geoJSON(data, {
      style: { color: "#f39c12", weight: 2, fillOpacity: 0.1 },
      onEachFeature: function (feature, layer) {
        const p = feature.properties;
        const content = `
          <div style="font-size: 13px; line-height: 1.4; color: #fff;">
            <h4 style="margin:0 0 8px; color:#f39c12;">📌 ${p.n_pos}</h4>
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
          </div>`;
        layer.bindPopup(content);
      }
    });
  });

  // Handle button clicks
  function showLayer(layerName) {
    if (activeLayer && map.hasLayer(activeLayer)) {
      map.removeLayer(activeLayer);
    }

    switch (layerName) {
      case 'wilaya': if (layer1) map.addLayer(layer1); activeLayer = layer1; break;
      case 'commune': if (layer2) map.addLayer(layer2); activeLayer = layer2; break;
      case 'pdau': if (layer3) map.addLayer(layer3); activeLayer = layer3; break;
      case 'pos': if (layer4) map.addLayer(layer4); activeLayer = layer4; break;
    }

    // Update button UI
    $('#layer-buttons button').removeClass('active');
    $(`#layer-buttons button[data-layer="${layerName}"]`).addClass('active');
  }

  // Event binding
  $('#layer-buttons button').on('click', function () {
    const selected = $(this).data('layer');
    showLayer(selected);
  });

  // Optional: show one by default
  // $(document).ready(() => showLayer('pdau'));
</script>

</body>
</html>
