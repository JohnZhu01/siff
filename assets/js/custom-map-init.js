import { LeafletMap, TileLayer, Marker} from "leaflet";

// Show the map in a page block or the event locator block.
document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("siff-event-locator") || document.getElementById("wp-custom-map");
  if (!container) {
    return;
  }
  const map = new LeafletMap(container).setView([49.39, -123.502055], 14);
// new TileLayer("https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png", {
//   maxZoom: 19,
//   attribution:
//     '&copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
// }).addTo(map);
new TileLayer("https://tileserver.memomaps.de/tilegen/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution:
    'Map <a href="https://memomaps.de/">memomaps.de</a> <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);
  const marker = new Marker([49.393968, -123.502055]).addTo(map);

  marker.bindPopup("<b>SIFF Location</b><br>Georgia Drive, Gibsons, BC<br>June 11–13, 2027").openPopup();
  
});

