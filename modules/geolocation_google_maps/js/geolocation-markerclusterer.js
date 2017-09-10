/**
 * MarkerClusterer handling.
 *
 * @property {String} markerClusterer.enable
 * @property {String} markerClusterer.imagePath
 * @property {Object} markerClusterer.styles
 */

if (
  typeof commonMapSettings.markerClusterer !== 'undefined'
  && commonMapSettings.markerClusterer.enable
  && typeof MarkerClusterer !== 'undefined'
) {

  /* global MarkerClusterer */

  var imagePath = '';
  if (commonMapSettings.markerClusterer.imagePath) {
    imagePath = commonMapSettings.markerClusterer.imagePath;
  }
  else {
    imagePath = 'https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m';
  }

  var markerClustererStyles = '';
  if (typeof commonMapSettings.markerClusterer.styles !== 'undefined') {
    markerClustererStyles = commonMapSettings.markerClusterer.styles;
  }

  map.addLoadedCallback(function (map) {
    new MarkerClusterer(
      map.googleMap,
      map.mapMarkers,
      {
        imagePath: imagePath,
        styles: markerClustererStyles
      }
    );
  });
}