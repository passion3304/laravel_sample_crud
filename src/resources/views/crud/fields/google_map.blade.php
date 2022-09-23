@php
    $field['map_options']['height'] = $field['map_options']['height'] ?? 400;
    $field['map_options']['locate'] = $field['map_options']['locate'] ?? true;
    $field['map_options']['default_lat'] = $field['map_options']['default_lat'] ?? config('services.google_places.default_lat', 29.97917);
    $field['map_options']['default_lng'] = $field['map_options']['default_lng'] ?? config('services.google_places.default_lng', 31.13426);
    $field['map_options']['language'] = $field['map_options']['language'] ?? app()->getLocale();
    $field['save_as'] = $field['save_as'] ?? false;
    $field['value'] = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';
@endphp
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
@if($field['map_options']['locate'])
    <button type="button" class="btn btn-sm btn-link float-right" location-button-unique-name="locationButton_{{$field['name']}}"><span class="la la-map-marker"></span>{{$field['map_options']['locate_button_text'] ?? trans('backpack::crud.google_map_locate')}}</button>
@endif
<div style="overflow: hidden">

    <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">

    @if(isset($field['prefix']) || isset($field['suffix']))
    <div class="input-group"> @endif
        @if(isset($field['prefix']))
            <div class="input-group-addon">{!! $field['prefix'] !!}</div> 
        @endif
    <input type="text"
        class="form-control"
        location-search-unique-name="locationSearch_{{$field['name']}}"
        data-init-function="bpFieldInitGoogleMapElement"
        data-google-address-field-name="{{$field['name']}}"
        data-google-default-lat="{{$field['map_options']['default_lat']}}"
        data-google-default-lng="{{$field['map_options']['default_lng']}}"
        data-google-address-inputs="{{ json_encode($field['save_as'] ? ['lat' => $field['save_as']['lat'], 'lng' => $field['save_as']['lng']] : [$field['name']]) }}"
        @include('crud::fields.inc.attributes')
    >
    @if(isset($field['suffix']))
        <div class="input-group-addon">{!! $field['suffix'] !!}</div>
     @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    <div style="width: 100%;height: {{$field['map_options']['height']}}px" map-unique-name="map_{{$field['name']}}"></div>
</div>

@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif

@include('crud::fields.inc.wrapper_end')

@push('crud_fields_styles')
@loadOnce('googleMapCss')
    <style>
        .ap-input-icon.ap-icon-pin {
            right: 5px !important;
        }

        .ap-input-icon.ap-icon-clear {
            right: 10px !important;
        }

        .pac-container {
            z-index: 1051;
        }
    </style>
@endLoadOnce
@endpush

@push('crud_fields_scripts')
@loadOnce('bpFieldInitGoogleMapElement')
    <script>
        function bpFieldInitGoogleMapElement(element) {
            //this script is async loaded so it does not prevent other scripts in page to load while this is fetched from outside url.
            //at somepoint our initialization script might run before the script is on page throwing undesired errors.
            //this makes sure that when this script is run, it has google available either on our field initialization or when the callback function is called.
            if(typeof google === "undefined") { return; }

            try {
                var addressConfig = element.data('google-address-inputs');
                var mainFieldName = element.data('google-address-field-name');
                var mapField = document.querySelector('[data-input-name="' + mainFieldName + '"]') ?? document.querySelector('[name="' + mainFieldName + '"]');
                if (mapField.value) {
                    var existingData = JSON.parse(mapField.value);
                    var latlng = new google.maps.LatLng(existingData.lat, existingData.lng)
                    var isDefault = false;
                } else {
                    var lat = JSON.stringify(element.data('google-default-lat'));
                    var lng = JSON.stringify(element.data('google-default-lng'));
                    var latlng = new google.maps.LatLng(lat, lng);
                    var isDefault = true;
                }
                
                const map = new google.maps.Map(document.querySelector('[map-unique-name="map_'+mainFieldName+'"]'), {
                    center: latlng,
                    zoom: 18,
                    mapTypeId: "roadmap",
                });
                infoWindow = new google.maps.InfoWindow();

                const locationButton = document.querySelector('[location-button-unique-name="locationButton_'+mainFieldName+'"]');

                locationButton.addEventListener("click", (e) => {
                    e.preventDefault();
                    // Try HTML5 geolocation.
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
                                savePosition(latlng);
                                map.setCenter(latlng);
                            },
                            () => {
                                handleLocationError(true, infoWindow, map.getCenter());
                            }
                        );
                    } else {
                        // Browser doesn't support Geolocation
                        handleLocationError(false, infoWindow, map.getCenter());
                    }
                });

                function handleLocationError(browserHasGeolocation, infoWindow, pos) {
                    infoWindow.setPosition(pos);
                    infoWindow.setContent(
                        browserHasGeolocation
                            ? "Error: The Geolocation service failed."
                            : "Error: Your browser doesn't support geolocation."
                    );
                    infoWindow.open(map);
                }

                const marker = new google.maps.Marker({
                    map,
                    position: latlng,
                    draggable: true
                });
                // drag response
                marker.addListener('dragend', function (e) {
                    savePosition(this.getPosition());
                });
                if (!isDefault) {
                    savePosition(latlng);
                }

                // Create the search box and link it to the UI element.
                const input = document.querySelector('[location-search-unique-name="locationSearch_'+mainFieldName+'"]');
                const searchBox = new google.maps.places.SearchBox(input);

                // Bias the SearchBox results towards current map's viewport.
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });

                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }


                    // For each place, get the icon, name and location.
                    const bounds = new google.maps.LatLngBounds();

                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }

                        savePosition(place.geometry.location);


                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });

                // saves the position on the input elements
                function savePosition(pos) {
                    var data = {};
                    data['lat'] = pos.lat();
                    data['lng'] = pos.lng();
                    marker.setPosition(pos);
                    mapField.value = JSON.stringify(data);
                    //user is saving on two inputs, update their values
                    if(Object.keys(addressConfig).length === 2) {
                        document.querySelector('[name="'+addressConfig.lat+'"]').value = data.lat;
                        document.querySelector('[name="'+addressConfig.lng+'"]').value = data.lng;
                    }

                }

                element.keydown(function(e) {
                    if ($('.pac-container').is(':visible') && e.keyCode == 13) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Make sure pac container is closed on modals (inline create)
                let modal = document.querySelector('.modal-dialog');
                if(modal) modal.addEventListener('click', e => document.querySelector('.pac-container').style.display = "none");
            } catch (e) {
                console.log(e);
            }

        }

        function initGoogleAddressAutocomplete() {
            $('[data-google-address]').each(function () {
                var element = $(this);
                var functionName = element.data('init-function');
                if (typeof window[functionName] === "function") {
                    window[functionName](element);
                }

            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3&key={{ $field['api_key'] ?? config('services.google_places.key') }}&libraries=places&callback=initGoogleAddressAutocomplete&language={{$field['map_options']['language']}}" async defer></script>
@endLoadOnce
@endpush
