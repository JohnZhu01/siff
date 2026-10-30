import { Icon, LeafletMap, Marker, TileLayer, Tooltip } from "leaflet";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import "leaflet/dist/leaflet.css";

Icon.Default.setDefaultOptions({
	iconUrl: markerIcon,
	iconRetinaUrl: markerIcon2x,
	shadowUrl: markerShadow,
});

const INITIAL_ZOOM = 12;
const INITIAL_LATLNG = [49.393968, -123.502055];

const mapContainer = document.querySelector("[data-locations-map]");
const map = new LeafletMap(mapContainer)
  .setView(INITIAL_LATLNG, INITIAL_ZOOM);

// Update Leaflet when the map container is resized
const resizeObserver = new ResizeObserver(() => {
	requestAnimationFrame(() => {
		map.invalidateSize({ pan: false });
	});
});

resizeObserver.observe(mapContainer);

new TileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
	maxZoom: 19,
	attribution:
		'&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
}).addTo(map);

try {
	const response = await fetch("/wp-json/bakery/v1/locations");
	if (!response.ok)
		throw new Error(`Failed to fetch locations: ${response.status}`);

	const locations = await response.json();
	const coordinates = [];

	locations.forEach((el) => {
		const {
			location_name = "",
			phone_number = "",
			email = "",
			opening_hours = [],
			address: {
				city = "",
				postal_code = "",
				province = "",
				street_address = "",
			} = {},
			map: { latitude = null, longitude = null } = {},
		} = el ?? {};
		if (latitude && longitude) {
			coordinates.push([Number(latitude), Number(longitude)]);

			const marker = new Marker([latitude, longitude]).addTo(map);

			const formattedPhoneNum =
				phone_number.replace(
					/^([2-9]\d{2})([2-9]\d{2})(\d{4})$/,
					"($1) $2-$3",
				) || "";

			marker.bindPopup(
				`<b>${location_name}</b><br/><p>${street_address}<br/>${city}, ${province} ${postal_code}</p><p>${formattedPhoneNum}</p><p>${email}</p>`,
				{},
			);
			const tooltipConfig = { direction: "bottom", permanent: true };
			new Tooltip(tooltipConfig)
				.setLatLng([latitude, longitude])
				.setContent(location_name)
				.addTo(map);
		}
	});

	// Fit all location markers within the map
	if (coordinates.length > 0) {
		map.fitBounds(coordinates, {
			padding: [40, 40],
			maxZoom: 12
		});
	}
} catch (err) {
	console.error(err);
}
