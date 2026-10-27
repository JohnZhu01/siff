import { LeafletMap, TileLayer, Marker} from "leaflet";

// Add a div with an id of wp-custom-map to show it in the front end
document.addEventListener("DOMContentLoaded", function () {
  const map = new LeafletMap("siff-event-locator").setView([49.39, -123.502055], 14);
  new TileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);
  const marker = new Marker([49.393968, -123.502055]).addTo(map);

  marker.bindPopup("<b>SIFF Event Location</b><br>I am a popup.").openPopup();
  
});

