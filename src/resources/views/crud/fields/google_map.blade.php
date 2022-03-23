@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

<div style="overflow: hidden">
    <input type="hidden"
           value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? json_encode($field['value']) : (isset($field['default']) ? $field['default'] : '' )) }}"
           name="{{ $field['name'] }}">
    <input type="search"
           class="form-control"
           id="locationSearch_{{$field['name']}}"
           data-init-function="bpFieldInitGoogleMapElement"
           data-google-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;}"
            @include('crud::fields.inc.attributes')
    >

    <div style="width: 100%;height: 400px" id="map_{{$field['name']}}"></div>

    <span class="btn btn-dark btn-block" id="locationButton_{{$field['name']}}">
        <i class="la la-map-pin"></i> {{ __('Get My Location') }}
    </span>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')
{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
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
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            var list = document.getElementsByTagName('script');
            var i = list.length, flag = false;
            while (i--) {
                if (list[i].src === 'https://maps.googleapis.com/maps/api/js?v=3&key={{ $field['api_key'] ?? config('services.google-places.key') }}&libraries=places&callback=initGoogleAddressAutocomplete') {
                    flag = true;
                    break;
                }
            }

            // if we didn't already find it on the page, add it
            if (!flag) {
                var tag = document.createElement('script');
                tag.src = 'https://maps.googleapis.com/maps/api/js?v=3&key={{ $field['api_key'] ?? config('services.google-places.key') }}&libraries=places&callback=initGoogleAddressAutocomplete';
                document.getElementsByTagName('body')[0].appendChild(tag);
            }
        </script>
        <script>


            function bpFieldInitGoogleMapElement(element) {
                try {
                    var $addressConfig = element.data('google-address');

                    var $field = $('[name="' + $addressConfig.field + '"]');
                    if ($field.val().length) {
                        var existingData = JSON.parse($field.val());
                        var latlng = new google.maps.LatLng(existingData.lat, existingData.lng)
                        var isDefault = false;
                    } else {
                        var lat = @json(config('services.google-places.default-map-center.lat', 29.97917))
                        var lng = @json(config('services.google-places.default-map-center.lng', 31.13426))
                        var latlng = new google.maps.LatLng(lat, lng)
                        var isDefault = true;
                    }
                    const map = new google.maps.Map(document.getElementById("map_"+$addressConfig.field), {
                        center: latlng,
                        zoom: 18,
                        mapTypeId: "roadmap",
                    });
                    infoWindow = new google.maps.InfoWindow();

                    const locationButton = document.getElementById("locationButton_"+$addressConfig.field);

                    // map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(locationButton);
                    locationButton.addEventListener("click", (e) => {
                        e.preventDefault();
                        // Try HTML5 geolocation.
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
                                    displayPosition(latlng);
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
                        displayPosition(this.getPosition());
                    });
                    if (!isDefault) {
                        displayPosition(latlng);
                    }

                    // Create the search box and link it to the UI element.
                    const input = document.getElementById("locationSearch_"+$addressConfig.field);
                    const searchBox = new google.maps.places.SearchBox(input);

                    // Bias the SearchBox results towards current map's viewport.
                    map.addListener("bounds_changed", () => {
                        searchBox.setBounds(map.getBounds());
                    });

                    let markers = [];

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

                            displayPosition(place.geometry.location);


                            if (place.geometry.viewport) {
                                // Only geocodes have viewport.
                                bounds.union(place.geometry.viewport);
                            } else {
                                bounds.extend(place.geometry.location);
                            }
                        });
                        map.fitBounds(bounds);
                    });

                    // displays a position on two <input> elements
                    function displayPosition(pos) {

                        var data = {};
                        data['lat'] = pos.lat();
                        data['lng'] = pos.lng();
                        marker.setPosition(pos);
                        $field.val(JSON.stringify(data));

                    }
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
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
