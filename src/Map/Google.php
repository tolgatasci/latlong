<?php

namespace Encore\Admin\Latlong\Map;

class Google extends AbstractMap
{
    /**
     * @var string
     */
    protected $api = '//maps.googleapis.com/maps/api/js?v=3.exp&key=%s&libraries=places';

    /**
     * {@inheritdoc}
     */
    public function applyScript(array $id)
    {
        $autoPosition = ($this->autoPosition)?'1':'0';
        return <<<EOT
        (function() {
            function init(name) {
                var lat = null;
                var lng = null;
                var locations = [];
                for(var i = 0; i < maps_list.length; i++)
                {

                    locations.push([maps_list[i]['action_name'], maps_list[i]['lat'], maps_list[i]['long'], i,maps_list[i]['order_name']])

                }

                var LatLng = new google.maps.LatLng(maps_list[0]['lat'], maps_list[0]['long']);

                var options = {
                    zoom: 11,
                    center: LatLng,
                    panControl: false,
                    zoomControl: true,
                    scaleControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                var flightPlanCoordinates = [];
                var container = document.getElementById("map_"+name);
                var map = new google.maps.Map(container, options);

                if (navigator.geolocation && {$autoPosition}) {
                  navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                      lat: position.coords.latitude,
                      lng: position.coords.longitude
                    };
                    map.setCenter(pos);
                    marker.setPosition(pos);

                    lat.val(position.coords.latitude);
                    lng.val(position.coords.longitude);

                  }, function() {

                  });
                }
 var infowindow = new google.maps.InfoWindow();
              var marker, i;
    var bounds = new google.maps.LatLngBounds();

    var mIcon = {
      path: google.maps.SymbolPath.CIRCLE,
      fillOpacity: 1,
      fillColor: '#fff',
      strokeOpacity: 1,
      strokeWeight: 1,
      strokeColor: '#333',
      scale: 12
    };
    for (i = 0; i < locations.length; i++) {

      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map,
        title: locations[i][4],
        icon: mIcon,
        label: {color: '#000000', fontSize: '12px', fontWeight: '600',
            text: (i+1) + ""}
      });
      flightPlanCoordinates.push(marker.getPosition());
      bounds.extend(marker.position);
      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {

          let name = "";
          if(name != locations[i][4]){
          name = locations[i][4];
          }
          infowindow.setContent("<h4>"+name+"</h4><p>"+locations[i][0]+"</p>");
          infowindow.open(map, marker);
        }
      })(marker, i));
    }

    map.fitBounds(bounds);

    var flightPath = new google.maps.Polyline({
      map: map,
      path: flightPlanCoordinates,
      strokeColor: "#FF0000",
      strokeOpacity: 1.0,
      strokeWeight: 2
    });

                var autocomplete = new google.maps.places.Autocomplete(
                    document.getElementById("search-{$id['lat']}{$id['lng']}")
                );
                autocomplete.bindTo('bounds', map);

                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    var place = autocomplete.getPlace();
                    var location = place.geometry.location;

                    if (place.geometry.viewport) {
                      map.fitBounds(place.geometry.viewport);
                    } else {
                      map.setCenter(location);
                      map.setZoom(18);
                    }

                    marker.setPosition(location);

                    lat.val(location.lat());
                    lng.val(location.lng());
                });
            }

            init('{$id['lat']}{$id['lng']}');
        })();
EOT;
    }
}
