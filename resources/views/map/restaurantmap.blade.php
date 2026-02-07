@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.live_tracking') }} - Restaurants</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.restaurant_tracking') }}
                    </li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">

                        <!-- LEFT LIST -->
                        <div class="col-lg-4">
                            <div class="table-responsive ride-list">
                                <input type="text"
                                       id="searchInput"
                                       oninput="searchRestaurant()"
                                       placeholder="Search restaurant...">

                                <div class="live-tracking-list"></div>
                            </div>
                        </div>

                        <!-- MAP -->
                        <div class="col-lg-8">
                            <div id="map" style="height:600px"></div>
                            <div id="legend">
                                <h3>{{ trans('lang.legend') }}</h3>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #legend {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 10px;
            margin: 11px;
            border: 1px solid #000;
        }
    </style>
@endsection

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

@section('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        var map;
        var markers = [];
        var base_url = '{!! asset('/images/') !!}';
        var sharedInfoWindow = null;


        /* ---------------- INIT ---------------- */

        $(document).ready(function () {
            initializeRestaurantMap();
        });

        /* ---------------- MAP INIT ---------------- */

        async function initializeRestaurantMap() {

            let attempts = 0;
            while (!window.mapType && attempts < 50) {
                await new Promise(r => setTimeout(r, 100));
                attempts++;
            }

            if (!window.mapType) window.mapType = 'OFFLINE';

            if (window.mapType === 'OFFLINE') {
                initLeafletMap();
                loadRestaurantData();
            } else {
                loadGoogleMaps();
            }
        }

        function loadGoogleMaps() {

            let attempts = 0;
            let interval = setInterval(() => {
                if (window.googleMapKey) {
                    clearInterval(interval);

                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${window.googleMapKey}`;
                    script.onload = () => {
                        initGoogleMap();
                        loadRestaurantData();
                    };
                    document.head.appendChild(script);
                }
                attempts++;
            }, 100);
        }

        /* ---------------- MAP SETUP ---------------- */

        function initLeafletMap() {
            map = L.map('map').setView([15.8281, 80.2897], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            initLegend();
        }

        function initGoogleMap() {
            sharedInfoWindow = new google.maps.InfoWindow();
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 10,
                center: { lat: 15.8281, lng: 80.2897 }
            });

            initLegend();
        }

        /* ---------------- LEGEND ---------------- */

        function initLegend() {
            const legend = document.getElementById('legend');

            const icons = {
                open: { name: 'Open Restaurant', icon: base_url + '/restaurant-open.png' },
                closed: { name: 'Closed Restaurant', icon: base_url + '/restaurant-closed.png' }
            };

            for (let key in icons) {
                let div = document.createElement('div');
                div.innerHTML = `<img src="${icons[key].icon}" width="20"> ${icons[key].name}`;
                legend.appendChild(div);
            }

            if (window.mapType !== 'OFFLINE' && map.controls) {
                map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
            }
        }

        /* ---------------- LOAD DATA ---------------- */

        function loadRestaurantData() {
            $.get('{{ url("/restaurant-map/data") }}', function (res) {
                if (res.success) {
                    renderRestaurants(res.data);
                }
            });
        }

        /* ---------------- RENDER ---------------- */

        function renderRestaurants(data) {
            $(".live-tracking-list").html('');
            markers = [];

            data.forEach((restaurant, index) => {
                const lat = restaurant.location.latitude;
                const lng = restaurant.location.longitude;

                /* 1. APPEND TO LEFT LIST */
                let html = `
            <div class="live-tracking-box track-from" data-index="${index}">
                <div class="live-tracking-inner">
                    <h3 class="drier-name">${restaurant.title}</h3>
                    <span class="badge ${restaurant.isOpen ? 'badge-success' : 'badge-danger'}">
                        ${restaurant.isOpen ? 'Open' : 'Closed'}
                    </span>
                </div>
            </div>`;
                $(".live-tracking-list").append(html);

                /* 2. DEFINE MARKER ICON & POPUP CONTENT */
                let iconImg = restaurant.isOpen
                    ? base_url + '/restaurant-open.png'
                    : base_url + '/restaurant-closed.png';

                let popupContent = `
            <div style="padding:5px;">
                <strong>${restaurant.title}</strong><br>
                Phone: ${restaurant.phoneNumber || '-'}
            </div>`;

                /* 3. HANDLE LEAFLET (OFFLINE MODE) */
                if (window.mapType === 'OFFLINE') {
                    let icon = L.icon({ iconUrl: iconImg, iconSize: [20, 20] });
                    let marker = L.marker([lat, lng], { icon }).addTo(map);

                    // Bind the popup but don't require a click
                    marker.bindPopup(popupContent, { closeButton: false });

                    // Hover Events for Leaflet
                    marker.on('mouseover', function (e) {
                        this.openPopup();
                    });
                    marker.on('mouseout', function (e) {
                        this.closePopup();
                    });

                    markers[index] = marker;

                    /* 4. HANDLE GOOGLE MAPS */
                } else {
                    let marker = new google.maps.Marker({
                        position: { lat, lng },
                        icon: { url: iconImg, scaledSize: new google.maps.Size(20, 20) },
                        map,
                        optimized: false // Required for hover/z-index stability
                    });

                    // Hover Events for Google Maps
                    marker.addListener('mouseover', function () {
                        sharedInfoWindow.setContent(popupContent);
                        sharedInfoWindow.open(map, marker);
                    });

                    marker.addListener('mouseout', function () {
                        sharedInfoWindow.close();
                    });

                    markers[index] = marker;
                }
            });
        }
        $(document).on('mouseenter', '.live-tracking-box', function () {
            const index = $(this).data('index');
            const marker = markers[index];

            if (!marker) return;

            if (window.mapType === 'OFFLINE') {
                marker.openPopup();
            } else {
                google.maps.event.trigger(marker, 'mouseover');
            }
        });

        $(document).on('mouseleave', '.live-tracking-box', function () {
            const index = $(this).data('index');
            const marker = markers[index];

            if (!marker) return;

            if (window.mapType === 'OFFLINE') {
                marker.closePopup();
            } else {
                google.maps.event.trigger(marker, 'mouseout');
            }
        });

        /* ---------------- SEARCH ---------------- */

        function searchRestaurant() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.live-tracking-box').forEach(el => {
                el.style.display = el.textContent.toLowerCase().includes(input)
                    ? 'block'
                    : 'none';
            });
        }
    </script>
@endsection
