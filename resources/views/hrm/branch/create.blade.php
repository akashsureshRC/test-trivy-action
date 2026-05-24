<style>
    .modal-header {
        background: var(--rc-gray-50, #f8f9fa) !important;
    }
    #branchMap {
        height: 250px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-top: 10px;
    }
    .coordinates-display {
        font-family: monospace;
        font-size: 12px;
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

{{ Form::open(['url' => 'branch', 'method' => 'post', 'id' => 'branchForm', 'data-custom-submit' => 'true']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Branch Name')]) }}
                <span class="error-text text-danger" id="error-name"></span>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                {{ Form::textarea('address', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Address'), 'rows' => 2, 'id' => 'branchAddress']) }}
                <span class="error-text text-danger" id="error-address"></span>
            </div>
        </div>

        <div class="col-md-12 mb-2">
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
                {{ Form::number('latitude', null, ['class' => 'form-control coordinates-display', 'id' => 'latitude', 'placeholder' => __('e.g. -26.2041'), 'step' => '0.00000001', 'min' => '-90', 'max' => '90']) }}
                <span class="error-text text-danger" id="error-latitude"></span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label']) }}
                {{ Form::number('longitude', null, ['class' => 'form-control coordinates-display', 'id' => 'longitude', 'placeholder' => __('e.g. 28.0473'), 'step' => '0.00000001', 'min' => '-180', 'max' => '180']) }}
                <span class="error-text text-danger" id="error-longitude"></span>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('attendance_radius', __('Geofence Radius (meters)'), ['class' => 'form-label']) }}
                <div class="d-flex align-items-center">
                    <input type="range" class="form-range me-3" id="radiusSlider" min="10" max="1000" step="10" value="100" style="flex: 1;">
                    {{ Form::number('attendance_radius', 100, ['class' => 'form-control', 'id' => 'attendance_radius', 'placeholder' => __('100'), 'min' => '10', 'max' => '10000', 'style' => 'width: 100px;']) }}
                    <span class="ms-2">m</span>
                </div>
                <small class="text-muted">{{ __('Distance employees must be within to clock in/out') }}</small>
                <span class="error-text text-danger" id="error-attendance_radius"></span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('clock_in_tolerance_minutes', __('Clock-In Tolerance (mins)'), ['class' => 'form-label']) }}
                {{ Form::number('clock_in_tolerance_minutes', 15, ['class' => 'form-control', 'min' => '0', 'max' => '60']) }}
                <small class="text-muted">{{ __('Minutes after scheduled start before marked late') }}</small>
                <span class="error-text text-danger" id="error-clock_in_tolerance_minutes"></span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('clock_out_tolerance_minutes', __('Clock-Out Tolerance (mins)'), ['class' => 'form-label']) }}
                {{ Form::number('clock_out_tolerance_minutes', 15, ['class' => 'form-control', 'min' => '0', 'max' => '60']) }}
                <small class="text-muted">{{ __('Minutes before scheduled end before marked early') }}</small>
                <span class="error-text text-danger" id="error-clock_out_tolerance_minutes"></span>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn btn-rc-primary']) }}
</div>
{{ Form::close() }}

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
// Global variables for map components
var branchMap = null;
var branchMarker = null;
var branchCircle = null;

// Update marker and circle position
function updateBranchPosition(lat, lng) {
    if (branchMarker && branchCircle && branchMap) {
        branchMarker.setLatLng([lat, lng]);
        branchCircle.setLatLng([lat, lng]);
        branchMap.setView([lat, lng], branchMap.getZoom());
    }
    $('#latitude').val(lat.toFixed(8));
    $('#longitude').val(lng.toFixed(8));
}

// Update circle radius
function updateBranchRadius(radius) {
    if (branchCircle) {
        branchCircle.setRadius(radius);
    }
}

// Wait for Leaflet to be available
function initBranchMap() {
    if (typeof L === 'undefined') {
        setTimeout(initBranchMap, 100);
        return;
    }
    
    // Prevent re-initialization
    if (branchMap !== null) {
        return;
    }

    // Default coordinates (South Africa center)
    let defaultLat = -26.2041;
    let defaultLng = 28.0473;
    let defaultRadius = 100;

    // Initialize map
    branchMap = L.map('branchMap').setView([defaultLat, defaultLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(branchMap);

    // Add marker
    branchMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(branchMap);
    
    // Add geofence circle
    branchCircle = L.circle([defaultLat, defaultLng], {
        color: '#973894',
        fillColor: '#973894',
        fillOpacity: 0.2,
        radius: defaultRadius
    }).addTo(branchMap);

    // Marker drag event
    branchMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateBranchPosition(pos.lat, pos.lng);
    });

    // Map click event
    branchMap.on('click', function(e) {
        updateBranchPosition(e.latlng.lat, e.latlng.lng);
    });

    // Invalidate map size when modal is shown
    setTimeout(function() {
        branchMap.invalidateSize();
    }, 300);
}

// Start initialization when document is ready
$(document).ready(function() {
    // Initialize map after a small delay to ensure modal is visible
    setTimeout(initBranchMap, 200);
    
    // Radius slider change
    $('#radiusSlider').on('input', function() {
        const radius = parseInt($(this).val());
        $('#attendance_radius').val(radius);
        updateBranchRadius(radius);
    });

    // Radius input change
    $('#attendance_radius').on('change', function() {
        const radius = parseInt($(this).val()) || 100;
        $('#radiusSlider').val(Math.min(radius, 1000));
        updateBranchRadius(radius);
    });

    // Manual coordinate change
    $('#latitude, #longitude').on('change', function() {
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        if (!isNaN(lat) && !isNaN(lng)) {
            updateBranchPosition(lat, lng);
        }
    });

    // Search address using Nominatim
    $('#searchAddressBtn').on('click', function() {
        const address = $('#branchAddress').val();
        if (!address) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> {{ __("Searching...") }}');

        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            dataType: 'json',
            data: {
                q: address,
                format: 'json',
                limit: 1
            },
            success: function(data) {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    updateBranchPosition(lat, lng);
                    if (branchMap) branchMap.setZoom(17);
                }
            },
            error: function(xhr, status, error) {
                console.error('Nominatim error:', status, error);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti ti-search"></i> {{ __("Search Address") }}');
            }
        });
    });

    // Use current location
    $('#useCurrentLocationBtn').on('click', function() {
        const btn = $(this);
        
        if (!navigator.geolocation) {
            return;
        }

        btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> {{ __("Getting location...") }}');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                updateBranchPosition(position.coords.latitude, position.coords.longitude);
                if (branchMap) branchMap.setZoom(17);
                btn.prop('disabled', false).html('<i class="ti ti-current-location"></i> {{ __("Use Current Location") }}');
            },
            function(error) {
                btn.prop('disabled', false).html('<i class="ti ti-current-location"></i> {{ __("Use Current Location") }}');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
});

$(document).on('submit', '#branchForm', function(e) {
    e.preventDefault();

    let form = $(this);
    $('.error-text').text('');

    $.ajax({
        url: form.attr('action'),
        method: form.attr('method'),
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#commonModal').modal('hide');
                form[0].reset();
                location.reload();
            }
        },
        error: function(xhr) {
            console.log(xhr); // debug
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $('#error-' + field).text(messages[0]);
                });
            }
        }
    });
});
</script>