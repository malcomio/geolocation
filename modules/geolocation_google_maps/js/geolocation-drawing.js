if (
  commonMapSettings.common_google_map_drawing_settings.polyline
  || commonMapSettings.common_google_map_drawing_settings.polygon
) {
  map.addLoadedCallback(function (map) {

    var locations = [];

    $('#' + map.id, context).find('.geolocation-map-locations .geolocation').each(function (index, location) {

      /** @var jQuery */
      location = $(location);
      locations.push(new google.maps.LatLng(Number(location.data('lat')), Number(location.data('lng'))));
    });

    if (!locations.length) {
      return;
    }

    if (map.settings.common_google_map_drawing_settings.polygon) {
      var polygon = new google.maps.Polygon({
        paths: locations,
        strokeColor: map.settings.common_google_map_drawing_settings.strokeColor,
        strokeOpacity: map.settings.common_google_map_drawing_settings.strokeOpacity,
        strokeWeight: map.settings.common_google_map_drawing_settings.strokeWeight,
        geodesic: map.settings.common_google_map_drawing_settings.geodesic,
        fillColor: map.settings.common_google_map_drawing_settings.fillColor,
        fillOpacity: map.settings.common_google_map_drawing_settings.fillOpacity
      });
      polygon.setMap(map.googleMap);
    }

    if (map.settings.common_google_map_drawing_settings.polyline) {
      var polyline = new google.maps.Polyline({
        path: locations,
        strokeColor: map.settings.common_google_map_drawing_settings.strokeColor,
        strokeOpacity: map.settings.common_google_map_drawing_settings.strokeOpacity,
        strokeWeight: map.settings.common_google_map_drawing_settings.strokeWeight,
        geodesic: map.settings.common_google_map_drawing_settings.geodesic
      });
      polyline.setMap(map.googleMap);
    }

  }, mapId);
}