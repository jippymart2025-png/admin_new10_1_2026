@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.live_tracking')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{trans('lang.live_tracking')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <!-- start row -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="table-responsive ride-list">
                            <input type="text" id="searchInput" oninput="searchDriver()" placeholder="Search Driver...">
                            <div class="live-tracking-list">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div id="map" style="height:450px"></div>
                        <div id="legend">
                            <h3>{{trans('lang.legend')}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #append_list12 tr {
            cursor: pointer;
        }
        #legend {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 10px;
            margin: 11px;
            border: 1px solid #000;
        }
        #legend h3 {
            margin-top: 0;
        }
        #legend img {
            vertical-align: middle;
        }
    </style>
    @endsection
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    @section('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script type="text/javascript">
        // ✅ SQL API VERSION - No Firebase!
        console.log('✅ Live Tracking using SQL API');

        var map;
        var marker;
        var markers = [];
        var map_data = [];
        var base_url = '{!! asset('/images/') !!}';
        var mapInitialized = false;

        // Wait for settings to load, then initialize map
        async function initializeLiveTracking() {
            console.log('✅ Initializing Live Tracking Map with SQL API');

            // Wait for mapType to be set by settings-loader.js
            let attempts = 0;
            while (!window.mapType && attempts < 50) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            if (!window.mapType) {
                console.warn('⚠️ mapType not set, defaulting to OFFLINE (OpenStreetMap)');
                window.mapType = 'OFFLINE';
            }

            console.log('📍 Map Type:', window.mapType);

            // Load appropriate map script
            if (window.mapType === 'OFFLINE') {
                // OpenStreetMap/Leaflet is already loaded in the head
                console.log('📍 Using OpenStreetMap (Leaflet)');
                InitializeGodsEyeMap();
                loadTrackingData();
            } else {
                // Load Google Maps API
                console.log('📍 Loading Google Maps API...');
                await loadGoogleMapsAPI();
            }
        }

        // Load Google Maps API
        async function loadGoogleMapsAPI() {
            // Wait for googleMapKey to be set
            let attempts = 0;
            while (!window.googleMapKey && attempts < 50) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            const apiKey = window.googleMapKey || 'AIzaSyAp4vhbe3AWgIj2lpS52M_kjgBKr-u13Xo';

            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                console.log('✅ Google Maps already loaded');
                InitializeGodsEyeMap();
                loadTrackingData();
                return;
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
            script.async = true;
            script.defer = true;

            script.onload = function() {
                console.log('✅ Google Maps API loaded successfully');
                InitializeGodsEyeMap();
                loadTrackingData();
            };

            script.onerror = function() {
                console.error('❌ Failed to load Google Maps API, falling back to OpenStreetMap');
                window.mapType = 'OFFLINE';
                InitializeGodsEyeMap();
                loadTrackingData();
            };

            document.head.appendChild(script);
        }

        // Load tracking data from SQL API
        function loadTrackingData() {
            $.ajax({
                url: '{{ route("map.getData") }}',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Live tracking data loaded from SQL:', response);

                    if (response.success && response.data) {
                        loadData(response.data);
                        searchDriver();
                    } else {
                        console.error('❌ Failed to load live tracking data');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error loading live tracking data:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        $(document).ready(function () {
            // Initialize live tracking after settings are loaded
            initializeLiveTracking();

            setTimeout(function () {
                $(".sidebartoggler").click();
            }, 500);

            $(document).on("click", ".ride-list .track-from", function () {
                var lat = $(this).data('lat');
                var lng = $(this).data('lng');
                var index = $(this).data('index');

                if (window.mapType == "OFFLINE" ){
                    map.setView([lat, lng], map.getZoom());
                    if(markers[index]){
                       markers[index].openPopup();
                    } else {
                       console.log("Marker at index " + index + " is undefined.");
                    }
                } else{
                    if (typeof google !== 'undefined' && google.maps) {
                        map.panTo(new google.maps.LatLng(lat, lng));
                        google.maps.event.trigger(markers[index], 'click');
                    }
                }
            });
        });

        // Cookie helper function
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        function InitializeGodsEyeMap() {
            var default_lat = getCookie('default_latitude') || 15.8281;
            var default_lng = getCookie('default_longitude') || 80.2897;
            var legend = document.getElementById('legend');

            console.log('🗺️ Initializing map with coordinates:', default_lat, default_lng);

            if (window.mapType == "OFFLINE" ){
                map = L.map('map').setView([default_lat, default_lng], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
            } else{
                if (typeof google !== 'undefined' && google.maps) {
                    var myLatlng = new google.maps.LatLng(default_lat, default_lng);
                    var infowindow = new google.maps.InfoWindow();
                    var mapOptions = {
                        zoom: 10,
                        center: myLatlng,
                        streetViewControl: false,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                    map = new google.maps.Map(document.getElementById("map"), mapOptions);
                    console.log('✅ Google Map initialized');
                } else {
                    console.error('❌ Google Maps API not available');
                }
            }

            var fliter_icons = {
                available: {
                    name: 'Available',
                    icon: base_url + '/available.png'
                },
                ontrip: {
                    name: 'In Transit',
                    icon: base_url + '/ontrip.png'
                }
            };

            for (var key in fliter_icons) {
                var type = fliter_icons[key];
                var name = type.name;
                var icon = type.icon;
                var div = document.createElement('div');
                div.innerHTML = '<img src="' + icon + '"> ' + name;
                legend.appendChild(div);
            }

            if (window.mapType == "OFFLINE" ){
                var lmaplegend  = L.control({ position: 'bottomleft' });
                lmaplegend.onAdd = function (map) {
                    var div = L.DomUtil.create('div', 'legend');
                    div.innerHTML = "<h4>Map Legend</h4>";
                    div.appendChild(legend);
                    return div;
                };
                lmaplegend.addTo(map);
                console.log('✅ OpenStreetMap initialized with legend');
            } else{
                if (map && typeof google !== 'undefined' && google.maps) {
                    map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
                }
            }

            mapInitialized = true;
            console.log('✅ Map initialization complete');
          }

        async function loadData(data) {
            console.log('✅ Loading ' + data.length + ' drivers/orders on map');

            for (let i = 0; i < data.length; i++) {
                val = data[i];
                var html = '';
                var driverId = '';
                var userId = '';

                if (val.flag == "in_transit") {
                    if (val.driver && val.driver.id) {
                        driverId = val.driver.id;
                    }
                } else {
                    driverId = val.id;
                }

                // Driver data is already in the response from SQL API
                let driver = val.driver || val;

                if (driver && driver.location) {
                    if (window.mapType == "OFFLINE" ){
                        html += '<div class="live-tracking-box track-from" data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '">';
                    }

                    if (val.flag == "in_transit") {
                        let user = val.author;

                        if (user) {
                            if (window.mapType != "OFFLINE" ){
                                html += '<div class="live-tracking-box track-from" data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '">';
                            }
                            html += '<div class="live-tracking-inner">';
                            html += '<span class="listicon"></span>';
                            html += '<h3 class="drier-name">{{trans("lang.driver_name")}} :  <span class="dvrname">' + (driver.firstName || '') + ' ' + (driver.lastName || '') + '</span></h3>';

                            if (user.firstName || user.lastName) {
                                html += '<h4 class="user-name">{{trans("lang.user_name")}} : ' + (user.firstName || '') + ' ' + (user.lastName || '') + '</h4>';
                            }

                            // Display pickup and destination locations
                            html += '<div class="location-ride">';
                            html += '<div class="from-ride"><span>' + (val.vendor && val.vendor.location ? val.vendor.location : 'Pickup location not available') + '</span></div>';

                            // Build destination address with proper null/undefined checks
                            var destinationParts = [];

                            if (user.shippingAddress) {
                                if (user.shippingAddress.line1 && user.shippingAddress.line1 !== "null" && user.shippingAddress.line1 !== '') {
                                    destinationParts.push(user.shippingAddress.line1);
                                }
                                if (user.shippingAddress.line2 && user.shippingAddress.line2 !== "null" && user.shippingAddress.line2 !== '') {
                                    destinationParts.push(user.shippingAddress.line2);
                                }
                                if (user.shippingAddress.city && user.shippingAddress.city !== "null" && user.shippingAddress.city !== '') {
                                    destinationParts.push(user.shippingAddress.city);
                                }
                                if (user.shippingAddress.country && user.shippingAddress.country !== "null" && user.shippingAddress.country !== '') {
                                    destinationParts.push(user.shippingAddress.country);
                                }
                            }

                            // Determine destination based on order type
                            var destination = '';
                            if (val.takeAway) {
                                destination = 'Customer pickup at restaurant';
                            } else if (destinationParts.length > 0) {
                                destination = destinationParts.join(', ');
                            } else {
                                destination = 'Destination address not available';
                            }

                            html += '<div class="to-ride"><span>' + destination + '</span></div>';
                            html += '</div>';

                            // Display order type (takeaway vs delivery)
                            var orderType = val.takeAway ? 'Takeaway' : 'Delivery';
                            var orderTypeClass = val.takeAway ? 'badge-warning' : 'badge-primary';
                            html += '<span class="badge ' + orderTypeClass + '">' + orderType + '</span>';
                            html += '&nbsp;&nbsp;<span class="badge badge-danger">In Transit</span>';
                            html += '&nbsp;&nbsp;<a href="/orders/edit/' + val.id + '" class="badge badge-info" target="_blank">{{trans("lang.order_id")}} : ' + val.id.substring(0, 7) + '</a>';
                            html += '</div>';
                            html += '</div>';
                        }
                    } else {
                        // Available driver
                        if (driver.firstName || driver.lastName) {
                            if (window.mapType != "OFFLINE" ){
                               html += '<div class="live-tracking-box track-from" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '">';
                            }
                            html += '<div class="live-tracking-inner">';
                            html += '<span class="listicon"></span>';
                            html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : <span class="dvrname">' + (driver.firstName || '') + ' ' + (driver.lastName || '') + '</span></h3>';
                            html += '<span class="badge badge-success">Available</span>';
                            html += '</div>';
                            html += '</div>';
                        }
                    }
                }

                $(".live-tracking-list").append(html);

                if (driver && driver.location && driver.location.latitude && driver.location.longitude) {
                    let iconImg = '';

                    if (val.flag == "available") {
                        iconImg = base_url + '/car_available.png';
                    } else {
                        iconImg = base_url + '/car_on_trip.png';
                    }

                    var content = `
                     <div class="p-2">
                         <h6>{{trans('lang.driver_name')}} : ${(driver.firstName || '') + " " + (driver.lastName || '')} </h6>
                         <h6>{{trans('lang.phone')}} : ${driver.phoneNumber || '-'} </h6>
                     </div>`;

                    if (window.mapType == "OFFLINE" ){
                        var customIcon = L.icon({
                            iconUrl: iconImg,
                            iconSize: [25, 25],
                        });
                        let marker = L.marker([driver.location.latitude, driver.location.longitude], { icon: customIcon }).addTo(map);
                        marker.bindPopup(content);
                        markers[i] = marker;
                        marker.on('click', function () {
                            marker.openPopup();
                        });

                        // Update location every 10 seconds
                        setInterval(function () {
                            locationUpdate(marker, driver);
                        }, 10000);
                    } else{
                        if (typeof google !== 'undefined' && google.maps) {
                            let marker = new google.maps.Marker({
                            position: new google.maps.LatLng(driver.location.latitude, driver.location.longitude),
                            icon: {
                                url: iconImg,
                                scaledSize: new google.maps.Size(25, 25)
                            },
                            map: map
                        });
                        let infowindow = new google.maps.InfoWindow({
                            content: content
                        });
                        marker.addListener('click', function () {
                            infowindow.open(map, marker);
                        });
                        markers[i] = marker;
                        marker.setMap(map);

                            // Update location every 10 seconds
                            setInterval(function () {
                                locationUpdate(marker, driver);
                            }, 10000);
                        }
                    }
                }
            }

            // Update location from SQL API
            async function locationUpdate(marker, driver) {
                $.ajax({
                    url: '{{ url("/map/driver") }}/' + driver.id + '/location',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data && response.data.location) {
                            let data = response.data;
                            if (data.location.latitude && data.location.longitude) {
                                if (window.mapType == "OFFLINE") {
                                    marker.setLatLng([data.location.latitude, data.location.longitude]);
                                } else {
                                    if (typeof google !== 'undefined' && google.maps) {
                                        marker.setPosition(new google.maps.LatLng(data.location.latitude, data.location.longitude));
                                    }
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating driver location:', error);
                    }
                });
            }
        }

        function searchDriver() {
            let input = document.getElementById('searchInput').value.trim().toLowerCase();
            let driverElements = document.querySelectorAll('.live-tracking-box');

            if (input === "") {
                driverElements.forEach(function(driver) {
                    driver.style.display = 'block'; // Show all drivers
                });
                return; // Exit early if input is blank
            }

            driverElements.forEach(function(driver) {
                let driverNameElement = driver.querySelector('.drier-name .dvrname');
                if (driverNameElement) {
                    let driverName = driverNameElement.textContent.trim().toLowerCase();
                    let driverNames = driverName.split(" ");
                    let firstName = driverNames[0];
                    let lastName = driverNames.slice(1).join(" "); // In case there is a middle name

                    // Check if input matches first name, last name, or full name
                    if (firstName.toLowerCase().includes(input) || lastName.toLowerCase().includes(input) || driverName.includes(input)) {
                        driver.style.display = 'block'; // Show driver
                    } else {
                        driver.style.display = 'none'; // Hide driver
                    }
                }
            });
        }
    </script>
    @endsection
