var birthPlace = document.getElementById('app_bundle_numerologie_birthPlace'),
    birthPlaceCoordinates = document.getElementById('app_bundle_numerologie_birthPlaceCoordinates'),
    autocomplete = new google.maps.places.Autocomplete((birthPlace), {})
;

autocomplete.addListener('place_changed', onPlaceChanged);

function onPlaceChanged() {
    let place = autocomplete.getPlace(),
        address = '';
    if (place.address_components) {
        address = [
            (place.address_components[0] && place.address_components[0].short_name || ''),
            (place.address_components[1] && place.address_components[1].short_name || ''),
            (place.address_components[2] && place.address_components[2].short_name || ''),
            (place.address_components[3] && place.address_components[3].short_name || '')
        ].join(', ');
        birthPlace.value = address;
    }
    if (place.geometry) {
        birthPlaceCoordinates.value = place.geometry.location.lat() + ',' + place.geometry.location.lng();
    }
}
