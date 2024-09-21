function initMap() {
  const map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 21.5121561, lng: -104.8927499 },
    zoom: 16,
  });
  const input = document.getElementById("pac-input");
  // Specify just the place data fields that you need.
  const autocomplete = new google.maps.places.Autocomplete(input, {
    types:['geocode']/*fields: ["place_id", "geometry", "name", "formatted_address"]*/,
  });

  autocomplete.bindTo("bounds", map);

  const infowindow = new google.maps.InfoWindow();
  const infowindowContent = document.getElementById("infowindow-content");

  infowindow.setContent(infowindowContent);

  const geocoder = new google.maps.Geocoder();
  const marker = new google.maps.Marker({ map: map });

  marker.addListener("click", () => {
    infowindow.open(map, marker);
  });
  autocomplete.addListener("place_changed", () => {
    infowindow.close();

    const place = autocomplete.getPlace();
    if (!place.place_id) {
      return;
    }

    geocoder
      .geocode({ placeId: place.place_id })
      .then(({ results }) => {
        map.setZoom(16);
        
        adr_comp_total = results[0].address_components.length;
        
        map.setCenter(results[0].geometry.location);
        marker.setPlace({
          placeId: place.place_id,
          location: results[0].geometry.location,
        });
        marker.setVisible(true);
        document.getElementById("place-lat").value = results[0].geometry.location.lat();
        document.getElementById("place-lng").value = results[0].geometry.location.lng();
        console.log(infowindowContent.children);
        console.log(place);
        
        infowindowContent.children["place-name"].textContent = place.name;
        colonia_municipio = place.adr_address;
        infowindowContent.children["place-address"].innerHTML = place.adr_address;
        console.log(colonia_municipio);
        console.log($('.extended-address').eq(0).text());
        
        colonia_municipio = $('.extended-address').eq(0).text() + ', ' + $('.postal-code').eq(0).text() + ', ' + $('.locality').eq(0).text() + ', ' + $('.region').eq(0).text() + ', ' + $('.country-name').eq(0).text();
        
        infowindowContent.children["place-address"].textContent = colonia_municipio;
        
        infowindow.open(map, marker);
      })
      .catch((e) => window.alert("Geocoder failed due to: " + e));
      
      
  });
}

window.initMap = initMap;