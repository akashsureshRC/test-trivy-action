<style>
    .modal-header {
        background: var(--rc-gray-50, #f8f9fa) !important;
    }
    #branchMap {
        height: 300px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .geofence-info {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .coordinates-display {
        font-family: monospace;
        font-size: 12px;
        color: #666;
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

<form id="geolocationForm" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="alert alert-info mb-3">
            <i class="ti ti-map-pin"></i>
            {{ __('Set the location and geofence radius for this branch. Employees must be within this radius to clock in/out.') }}
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-group">
                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('address', $branch->address, ['class' => 'form-control', 'placeholder' => __('Enter full address'), 'rows' => 2, 'id' => 'branchAddress']) }}
                    <small class="text-muted">{{ __('Enter address and click "Search" or click on the map to set location') }}</small>
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" id="searchAddressBtn">
                    <i class="ti ti-search"></i> {{ __('Search Address') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" id="useCurrentLocationBtn">
                    <i class="ti ti-current-location"></i> {{ __('Use Current Location') }}
                </button>
            </div>

            <div class="col-md-12 mb-3">
                <div id="branchMap"></div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label']) }}
                    {{ Form::number('latitude', $branch->latitude, ['class' => 'form-control coordinates-display', 'id' => 'latitude', 'step' => '0.00000001', 'min' => '-90', 'max' => '90', 'required' => true]) }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label']) }}
                    {{ Form::number('longitude', $branch->longitude, ['class' => 'form-control coordinates-display', 'id' => 'longitude', 'step' => '0.00000001', 'min' => '-180', 'max' => '180', 'required' => true]) }}
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <div class="form-group">
                    {{ Form::label('attendance_radius', __('Geofence Radius (meters)'), ['class' => 'form-label']) }}
                    <div class="d-flex align-items-center">
                        <input type="range" class="form-range me-3" id="radiusSlider" min="10" max="1000" step="10" value="{{ $branch->attendance_radius ?? 100 }}" style="flex: 1;">
                        {{ Form::number('attendance_radius', $branch->attendance_radius ?? 100, ['class' => 'form-control', 'id' => 'attendance_radius', 'min' => '10', 'max' => '10000', 'required' => true, 'style' => 'width: 100px;']) }}
                        <span class="ms-2">m</span>
                    </div>
                    <small class="text-muted">{{ __('Drag slider or enter value. Employees must be within this distance to clock in/out.') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('clock_in_tolerance_minutes', __('Clock-In Tolerance'), ['class' => 'form-label']) }}
                    <div class="input-group">
                        {{ Form::number('clock_in_tolerance_minutes', $branch->clock_in_tolerance_minutes ?? 15, ['class' => 'form-control', 'min' => '0', 'max' => '60']) }}
                        <span class="input-group-text">{{ __('minutes') }}</span>
                    </div>
                    <small class="text-muted">{{ __('Grace period before marked as late') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('clock_out_tolerance_minutes', __('Clock-Out Tolerance'), ['class' => 'form-label']) }}
                    <div class="input-group">
                        {{ Form::number('clock_out_tolerance_minutes', $branch->clock_out_tolerance_minutes ?? 15, ['class' => 'form-control', 'min' => '0', 'max' => '60']) }}
                        <span class="input-group-text">{{ __('minutes') }}</span>
                    </div>
                    <small class="text-muted">{{ __('Grace period before marked as early leaving') }}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-rc-primary">{{ __('Save Geolocation') }}</button>
    </div>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
$(document).ready(function() {
    // Default coordinates (South Africa center) if no coordinates set
    let defaultLat = {{ $branch->latitude ?? -26.2041 }};
    let defaultLng = {{ $branch->longitude ?? 28.0473 }};
    let defaultRadius = {{ $branch->attendance_radius ?? 100 }};

    // Initialize map
    const map = L.map('branchMap').setView([defaultLat, defaultLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add marker
    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    
    // Add geofence circle
    let circle = L.circle([defaultLat, defaultLng], {
        color: '#973894',
        fillColor: '#973894',
        fillOpacity: 0.2,
        radius: defaultRadius
    }).addTo(map);

    // Update marker and circle position
    function updatePosition(lat, lng) {
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        map.setView([lat, lng], map.getZoom());
        $('#latitude').val(lat.toFixed(8));
        $('#longitude').val(lng.toFixed(8));
    }

    // Update circle radius
    function updateRadius(radius) {
        circle.setRadius(radius);
    }

    // Marker drag event
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updatePosition(pos.lat, pos.lng);
    });

    // Map click event
    map.on('click', function(e) {
        updatePosition(e.latlng.lat, e.latlng.lng);
    });

    // Radius slider change
    $('#radiusSlider').on('input', function() {
        const radius = parseInt($(this).val());
        $('#attendance_radius').val(radius);
        updateRadius(radius);
    });

    // Radius input change
    $('#attendance_radius').on('change', function() {
        const radius = parseInt($(this).val());
        $('#radiusSlider').val(Math.min(radius, 1000));
        updateRadius(radius);
    });

    // Manual coordinate change
    $('#latitude, #longitude').on('change', function() {
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        if (!isNaN(lat) && !isNaN(lng)) {
            updatePosition(lat, lng);
        }
    });

    // Search address using Nominatim
    $('#searchAddressBtn').on('click', function() {
        const address = $('#branchAddress').val();
        if (!address) {
            toastr.warning('{{ __("Please enter an address to search") }}');
            return;
        }

        $(this).prop('disabled', true).html('<i class="ti ti-loader"></i> {{ __("Searching...") }}');

        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            data: {
                q: address,
                format: 'json',
                limit: 1
            },
            success: function(data) {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    updatePosition(lat, lng);
                    map.setZoom(17);
                    toastr.success('{{ __("Location found") }}');
                } else {
                    toastr.warning('{{ __("Address not found. Try a different search term.") }}');
                }
            },
            error: function() {
                toastr.error('{{ __("Error searching for address") }}');
            },
            complete: function() {
                $('#searchAddressBtn').prop('disabled', false).html('<i class="ti ti-search"></i> {{ __("Search Address") }}');
            }
        });
    });

    // Use current location
    $('#useCurrentLocationBtn').on('click', function() {
        if (!navigator.geolocation) {
            toastr.error('{{ __("Geolocation is not supported by your browser") }}');
            return;
        }

        $(this).prop('disabled', true).html('<i class="ti ti-loader"></i> {{ __("Getting location...") }}');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                updatePosition(position.coords.latitude, position.coords.longitude);
                map.setZoom(17);
                toastr.success('{{ __("Current location set") }}');
                $('#useCurrentLocationBtn').prop('disabled', false).html('<i class="ti ti-current-location"></i> {{ __("Use Current Location") }}');
            },
            function(error) {
                let errorMsg = '{{ __("Unable to get your location") }}';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = '{{ __("Location permission denied") }}';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = '{{ __("Location information unavailable") }}';
                        break;
                    case error.TIMEOUT:
                        errorMsg = '{{ __("Location request timed out") }}';
                        break;
                }
                toastr.error(errorMsg);
                $('#useCurrentLocationBtn').prop('disabled', false).html('<i class="ti ti-current-location"></i> {{ __("Use Current Location") }}');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });

    // Invalidate map size when modal is shown (fix for hidden map rendering)
    setTimeout(function() {
        map.invalidateSize();
    }, 500);

    // Form submission
    $('#geolocationForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("branch.geolocation.update", $branch->id) }}',
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#commonModal').modal('hide');
                    toastr.success(response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function(field, messages) {
                        errorMsg += messages[0] + '<br>';
                    });
                    toastr.error(errorMsg);
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            }
        });
    });
});
</script>
