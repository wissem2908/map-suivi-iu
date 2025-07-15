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
  <script src="https://cdn.maptiler.com/maptiler-sdk-js/v3.5.0/maptiler-sdk.umd.min.js"></script>
  <link href="https://cdn.maptiler.com/maptiler-sdk-js/v3.5.0/maptiler-sdk.css" rel="stylesheet" />
  <script src="https://cdn.maptiler.com/leaflet-maptilersdk/v4.1.0/leaflet-maptilersdk.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

  <style>
    html,
    body {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #222;
    }

    #map {
      height: 100vh;
    }

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
      flex-wrap: wrap;
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

    .leaflet-popup-content-wrapper {
      background-color: #2c2c2c;
      color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }

    .leaflet-popup-tip {
      background-color: #2c2c2c;
    }

    .geojson-label {
      background-color: rgba(255, 255, 255, 0);
      color: #838383ff;
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 4px;
      padding: 4px 10px;
      border-radius: 8px;
      border: 0px solid #666;
      text-align: center;
      white-space: nowrap;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .wilaya-label {
      color: #d1d0d0ff;
      font-weight: bold;
      text-align: center;
      white-space: nowrap;
      pointer-events: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>

<body>

  <!-- Layer Buttons -->
  <div id="layer-buttons">
    <button data-layer="wilaya">Wilaya</button>
    <button data-layer="commune">Commune</button>
    <button data-layer="pdau">PDAU</button>
    <button data-layer="pos">POS</button>
  </div>

  <div id="map"></div>

  <script>
    const map = L.map('map', {
      center: [30, 5],
      zoom: 5,
      attributionControl: false,
      zoomControl: false
    });

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri',
      maxZoom: 16
    }).addTo(map);

    function formatDate(timestamp) {
      if (!timestamp) return "N/A";
      const date = new Date(timestamp);
      return date.toLocaleDateString('fr-FR');
    }

    let layer1, layer2, layer3, layer4;
    let wilayaLabels;

    // Western Sahara
    $.getJSON('geojson/M_Final.geojson', function (data) {
      const westernSaharaLayer = L.geoJSON(data, {
        style: {
          color: "#333",
          weight: 1,
          fillOpacity: 0
        }
      }).addTo(map);

      const customLatLng = L.latLng(24.5, -13.5);
      L.tooltip({
        permanent: true,
        direction: 'center',
        className: 'geojson-label'
      })
        .setContent('WESTERN SAHARA')
        .setLatLng(customLatLng)
        .addTo(map);
    });

    // Wilaya
    $.getJSON('geojson/Wilaya_old.geojson', function (data) {
      layer1 = L.geoJSON(data, {
        style: { color: "#e74c3c", weight: 2, fillOpacity: 0.1 }
      });

      wilayaLabels = L.layerGroup();
      data.features.forEach(function (feature) {
        const name = feature.properties.nom_wilaya;
        const center = turf.centerOfMass(feature).geometry.coordinates;
        const bbox = turf.bbox(feature);
        const width = bbox[2] - bbox[0];
        const height = bbox[3] - bbox[1];

        let fontSize = Math.min(width, height) * 3;
        fontSize = Math.max(4, Math.min(14, fontSize));

        const labelIcon = L.divIcon({
          className: 'wilaya-label',
          html: `<div style="font-size:${fontSize}px">${name}</div>`,
          iconSize: null
        });

        const labelMarker = L.marker([center[1], center[0]], {
          icon: labelIcon,
          interactive: false
        });

        wilayaLabels.addLayer(labelMarker);
      });
    });

    // Commune
    $.getJSON('geojson/Commune.geojson', function (data) {
      layer2 = L.geoJSON(data, {
        style: {
          color: "#3498db",
          weight: 2,
          fillOpacity: 0.1
        }
      });
    });

    // PDAU
    $.getJSON('geojson/Pdau.geojson', function (data) {
      layer3 = L.geoJSON(data, {
        style: {
          color: "#2ecc71",
          weight: 2,
          fillOpacity: 0.1
        },
        onEachFeature: function (feature, layer) {
          const p = feature.properties;
          const content = `
            <div style="font-family: 'Segoe UI', sans-serif; font-size: 13px; color: #eee; background: #1e1e1e; padding: 12px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.6); max-width: 250px;">
              <h4 style="margin: 0 0 8px; color: #2ecc71;">PDAU Information</h4>
              <div><span style="color:#aaa;">Commune:</span> <strong>${p.commune_1}</strong></div>
              <div><span style="color:#aaa;">Bureau:</span> ${p.bureau_det}</div>
              <div><span style="color:#aaa;">Type étude:</span> ${p.etude}</div>
              <div><span style="color:#aaa;">État:</span> ${p.etat_d_ava}</div>
            </div>`;
          layer.bindPopup(content);
        }
      });
    });

    // POS
    $.getJSON('geojson/Pos.geojson', function (data) {
      layer4 = L.geoJSON(data, {
        style: {
          color: "#f39c12",
          weight: 2,
          fillOpacity: 0.1
        },
        onEachFeature: function (feature, layer) {
          const p = feature.properties;
          const content = `
            <div style="font-size: 13px; line-height: 1.4; color: #fff;">
              <h4 style="margin:0 0 8px; color:#f39c12;">📌 ${p.n_pos}</h4>
              <strong>Nom:</strong> ${p.nom}<br>
              <strong>Commune:</strong> ${p.commune}<br>
              <strong>Bureau:</strong> ${p.bureauetud}<br>
              <strong>Observations:</strong> ${p.observatio}<br>
            </div>`;
          layer.bindPopup(content);
        }
      });
    });

    // Nouvelle fonction : toggle couche (pas remplacement)
    function toggleLayer(layerName, buttonElement) {
      let layer;
      switch (layerName) {
        case 'wilaya':
          layer = layer1;
          break;
        case 'commune':
          layer = layer2;
          break;
        case 'pdau':
          layer = layer3;
          break;
        case 'pos':
          layer = layer4;
          break;
      }

      if (map.hasLayer(layer)) {
        map.removeLayer(layer);
        $(buttonElement).removeClass('active');
      } else {
        map.addLayer(layer);
        $(buttonElement).addClass('active');
      }

      if (layerName === 'wilaya') {
        if (map.hasLayer(wilayaLabels)) {
          map.removeLayer(wilayaLabels);
        } else {
          map.addLayer(wilayaLabels);
        }
      }
    }

    $('#layer-buttons button').on('click', function () {
      const selected = $(this).data('layer');
      toggleLayer(selected, this);
    });
  </script>

</body>

</html>
