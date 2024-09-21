let autocomplete;
let domicilio_input;
let cp_input;
let con_colonia = 0;
let con_municipio = 0;
let iconoPersonalizado;
let marker;
colonia_municipio="";
function initAutocomplete() {
    domicilio_input = document.querySelector("#domicilio");
    cp_input = document.querySelector("#cp");

    autocomplete = new google.maps.places.Autocomplete(domicilio_input, {
        componentRestrictions: { country: ["mx"] },
        fields: ["address_components", "geometry", "place_id", "name"],
        types: ["geocode"],
    });
    auto = autocomplete;
    
    iconoPersonalizado = {
        url: 'img/marcadores-1.png',//'http://maps.google.com/mapfiles/ms/icons/green-dot.png', // URL del ícono
        scaledSize: new google.maps.Size(32, 40), // Tamaño del ícono
        origin: new google.maps.Point(0, 0), // Origen del ícono
        anchor: new google.maps.Point(16, 40) // Punto de anclaje del ícono
    };
    
    //domicilio_input.focus();
    initMap();    
    
    autocomplete.addListener("place_changed", fillInAddress);
    
}
window.initMap = initMap;
function fillInAddress() {
    con_colonia = 0;
    con_municipio = 0;
    marcador();

    /**************************************************/
    place = autocomplete.getPlace();
    let domicilio = "";
    let cp = "";
    $('#form__colonia').html('<input placeholder=" " id="colonia" name="colonia" required class="form__input read" readonly value="<?php if(isset($colonia)) echo $colonia; ?>"/><label class="full-field form__label">Colonia</label>');
    document.querySelector("#colonia").value = '';
    for ( component of place.address_components) {
        
        componentType = component.types[0];
        //console.log(componentType);
        //console.log(componentType.includes('sublocality_level_1'));
        if(componentType.includes('sublocality_level_1')){
            con_colonia = 1;
        }
        if(componentType.includes('locality')){
            con_municipio = 1;
        }
        //console.log(componentType[]);
        switch (componentType) {
        case "street_number": {
            domicilio = `${component.long_name}`;   
            
            break;
        }

        case "route": {
            domicilio = component.short_name +" "+domicilio;
            break;
        }

        case "postal_code": {
            cp = `${component.long_name}${cp}`;
            break;
        }

        case "postal_code_suffix": {
            cp = `${postcode}-${component.long_name}`;
            break;
        }
                
        case "sublocality_level_1": {
            document.querySelector("#colonia").value = component.short_name;
            break;
        }
        case "locality":
            document.querySelector("#localidad").value = component.long_name;
            
            break;
        case "administrative_area_level_1": {
            document.querySelector("#estado").value = component.long_name;
            break;
        }
        case "country":
            document.querySelector("#pais").value = component.long_name;
            break;
        }
    }

    
    if(con_colonia == 0){
        //selecciona colonia a partir del codigo postal
        $('#form__colonia').html('<input type="text" name="cols_cp" id="cols_cp" placheholder=" " value="" style="display:none" readonly/><select class="form__input demarc" id="colonia_" name="colonia"></select><label class="form__label sexo_afiliacion" for="afiliacion">Colonia</label>');
        $.post("ajax/colonias_cp.php", { cp:cp }, function(data){
            
            cols_cp_temp = '';
            json = jQuery.parseJSON(data);
            for(i = 0; i< json.length; i++){
                $('#colonia_').append($('<option>', {
                    value: json[i]['colonia'],
                    text: json[i]['colonia']
                }));
                cols_cp_temp = cols_cp_temp + json[i]['colonia'] + ',';
            }
            
            $('#cols_cp').val(cols_cp_temp);
        });
    }
    if(con_municipio == 0){
        //selecciona colonia a partir del codigo postal
        $('#ciudad').val('Tepic');
    }
    
    
    ciudad_txt = $('#ciudad').val();
    if(con_municipio == 1 && ciudad_txt == ''){
        $('#ciudad').val('Tepic');
        $('#demarc').html('<option value="0">- Demarcaci&#243;n -</option>');
                $('#secc').html('<option value="0">- Secci&#243;n -</option>');
                dems_temp = "";
                dems_val = $('#dems').val();
        lvl = $('.ghost_lvl')[0].id;
                
                switch(lvl){
                    case "1":
                    case "2":
                    case "3":
                    case "8":
                        $.post("ajax/demarcaciones.php", {mun: $('#ciudad').val() }, function(data){
                            json = jQuery.parseJSON(data);
                                for(i = 0; i< json.length; i++){
                                    $('#demarc').append($('<option>', {
                                        value: json[i]['dem'],
                                        text: json[i]['dem']
                                    }));
                                    dems_temp = dems_temp + json[i]['dem'] + ",";
                                }
                                $('#dems').val(dems_temp);
                        });
                        break;
                    case "4":
                        
                        dems_val = dems_val.split(',');
                        for(i = 0; i< dems_val.length - 1 ; i++){
                            $('#demarc').append($('<option>', {
                                value: dems_val[i],
                                text: dems_val[i]
                            }));
                            dems_temp = dems_temp + dems_val[i] + ",";
                        }
                        $('#dems').val(dems_temp);
                        break;
                    case "5":
                    case "6":
                    case "7":
                        dems_val = dems_val.slice(0,-1);
                        $('#demarc').append('<option value="'+dems_val+'">'+dems_val+'</option>');
                        break;
                }
    }
    
    
    domicilio_input.value = domicilio;
    cp_input.value = cp;
    
    
    $.post("ajax/localidades.php", function(data){
        //console.log(data);
        json = jQuery.parseJSON(data);
        for(i = 0; i< json.length; i++){
            if($('#localidad').val() == json[i]['localidad']){
                document.querySelector("#ciudad").value = json[i]['municipio'];
                $('#demarc').html('<option value="0">- Demarcaci&#243;n -</option>');
                $('#secc').html('<option value="0">- Secci&#243;n -</option>');
                dems_temp = "";
                dems_val = $('#dems').val();
                lvl = $('.ghost_lvl')[0].id;
                
                switch(lvl){
                    case "1":
                    case "2":
                    case "3":
                    case "8":
                        $.post("ajax/demarcaciones.php", {mun: $('#ciudad').val() }, function(data){
                            json = jQuery.parseJSON(data);
                                for(i = 0; i< json.length; i++){
                                    $('#demarc').append($('<option>', {
                                        value: json[i]['dem'],
                                        text: json[i]['dem']
                                    }));
                                    dems_temp = dems_temp + json[i]['dem'] + ",";
                                }
                                $('#dems').val(dems_temp);
                        });
                        break;
                    case "4":
                        
                        dems_val = dems_val.split(',');
                        for(i = 0; i< dems_val.length - 1 ; i++){
                            $('#demarc').append($('<option>', {
                                value: dems_val[i],
                                text: dems_val[i]
                            }));
                            dems_temp = dems_temp + dems_val[i] + ",";
                        }
                        $('#dems').val(dems_temp);
                        break;
                    case "5":
                    case "6":
                    case "7":
                        dems_val = dems_val.slice(0,-1);
                        $('#demarc').append('<option value="'+dems_val+'">'+dems_val+'</option>');
                        break;
                }
                
                
                break;
            }
        }
    });
    
}

function initMap() {

    
   map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 21.5121561, lng: -104.8927499 },
    zoom: 16,
  });
   /* 
   marker = new google.maps.Marker({ position: { lat: 21.5121561, lng: -104.8927499 }, map: map, draggable: true });

  marker.addListener("click", () => {
    infowindow.open(map, marker);
  });*/
    
  auto.bindTo("bounds", map);

  infowindow = new google.maps.InfoWindow();
  infowindowContent = document.getElementById("infowindow-content");

  infowindow.setContent(infowindowContent);

  geocoder = new google.maps.Geocoder();
  

   
}

function marcador(){
    if (marker) {
        marker.setMap(null); // Elimina el marcador del mapa
    }

    let lat, lng;
    
    lugar = auto.getPlace();
    if (!lugar.place_id) {
        return;
    }
    
    
    geocoder.geocode({ placeId: lugar.place_id }).then(({ results }) => {
        document.getElementById("place-lat").value = results[0].geometry.location.lat();
        document.getElementById("place-lng").value = results[0].geometry.location.lng();
        lat = document.getElementById("place-lat").value;
        lng = document.getElementById("place-lng").value;
        
        map.setCenter(results[0].geometry.location);
        
        marker = new google.maps.Marker({
            position:{
                lat:parseFloat(lat), 
                lng:parseFloat(lng)},
            map: map, 
            draggable: true,
            icon: iconoPersonalizado
        });
    
        marker.setVisible(true);
        marker.setDraggable(true); 
    
        marker.addListener("click", () => {
            infowindow.open(map, marker);
        });
    infowindowContent.children["place-name"].textContent = lugar.name;
        
        colonia_municipio="";
        for (var i=2; i<lugar.address_components.length-1; i++) {
            colonia_municipio += lugar.address_components[i].long_name;
            if(i!=lugar.address_components.length-2){
               colonia_municipio += ', '
            }
        }
        infowindowContent.children["place-address"].innerHTML = colonia_municipio;
        
        infowindow.open(map, marker);
        // Evento dragend para actualizar las coordenadas en el formulario
        marker.addListener("dragend", () => {
            document.getElementById("place-lat").value = marker.getPosition().lat().toFixed(7);
            document.getElementById("place-lng").value = marker.getPosition().lng().toFixed(7);
        });
    }).catch((e) => window.alert("Geocoder fall&#243;: " + e));
    
   
    
}

function marcador_primero(latitud, longitud) {
    if (marker) {
        marker.setMap(null); // Elimina el marcador del mapa
    }

    let lat, lng;
    
    lat = $('#place-lat').val();
    lng = $('#place-lng').val();
    
    map.setCenter({ lat: parseFloat(lat), lng: parseFloat(lng) });
    
    marker = new google.maps.Marker({
        position: { lat: parseFloat(lat), lng: parseFloat(lng) },
        map: map, 
        draggable: true,
        icon: iconoPersonalizado
    });
    
    marker.setVisible(true);
    marker.setDraggable(true); 
    
    marker.addListener("click", () => {
        infowindow.open(map, marker);
    });
    
    infowindowContent.children["place-name"].textContent = $('#domicilio').val();
    infowindowContent.children["place-address"].innerHTML = $('#colonia').val() + ", " + $('#ciudad').val() + ", " + $('#estado').val() + ", " + $('#pais').val();
    
    infowindow.open(map, marker);
    
    // Evento dragend para actualizar las coordenadas en el formulario
    marker.addListener("dragend", () => {
        document.getElementById("place-lat").value = marker.getPosition().lat().toFixed(7);
        document.getElementById("place-lng").value = marker.getPosition().lng().toFixed(7);
    });
}
