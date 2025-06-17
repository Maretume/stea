@extends('layouts.app')

@section('title', 'Tambah Kantor')
@section('page-title', 'Tambah Kantor Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Kantor Baru
                </h5>
            </div>
            
            <form method="POST" action="{{ route('offices.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Nama Kantor <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Contoh: Kantor Pusat Jakarta" required maxlength="100">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" name="latitude" id="latitude" 
                                   class="form-control @error('latitude') is-invalid @enderror" 
                                   value="{{ old('latitude') }}" 
                                   step="0.000001" min="-90" max="90"
                                   placeholder="Contoh: -6.2088" required>
                            @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" name="longitude" id="longitude" 
                                   class="form-control @error('longitude') is-invalid @enderror" 
                                   value="{{ old('longitude') }}" 
                                   step="0.000001" min="-180" max="180"
                                   placeholder="Contoh: 106.8456" required>
                            @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="radius" class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" name="radius" id="radius" 
                                   class="form-control @error('radius') is-invalid @enderror" 
                                   value="{{ old('radius', 100) }}" 
                                   min="10" max="1000" required>
                            @error('radius')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Radius area untuk validasi absensi (10-1000 meter)</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select name="is_active" id="is_active" class="form-select">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Map Preview -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Preview Lokasi</h6>
                        </div>
                        <div class="card-body">
                            <div id="map" style="height: 300px; border-radius: 8px;"></div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentLocation()">
                                    <i class="fas fa-crosshairs me-1"></i>Gunakan Lokasi Saat Ini
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="searchLocation()">
                                    <i class="fas fa-search me-1"></i>Cari Lokasi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Location Helper -->
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Tips Penggunaan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <ul class="mb-0">
                                    <li><strong>Drag marker</strong> pada peta untuk memindahkan lokasi kantor</li>
                                    <li><strong>Gunakan tombol GPS</strong> untuk menggunakan lokasi saat ini</li>
                                    <li><strong>Gunakan pencarian</strong> untuk mencari alamat atau nama tempat</li>
                                    <li><strong>Area merah</strong> menunjukkan radius absensi yang akan diterapkan</li>
                                    <li><strong>Radius yang disarankan:</strong> 50-200 meter</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('offices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Simpan Kantor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
.leaflet-container {
    border-radius: 8px;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
let map;
let marker;
let circle;

$(document).ready(function() {
    initMap();

    // Update map when coordinates change
    $('#latitude, #longitude').on('input', function() {
        updateMapFromInputs();
    });

    // Update circle when radius changes
    $('#radius').on('input', function() {
        updateCircle();
    });
});

function initMap() {
    const lat = parseFloat(document.getElementById('latitude').value) || -6.200000;
    const lng = parseFloat(document.getElementById('longitude').value) || 106.816666;
    const radius = parseInt(document.getElementById('radius').value) || 100;

    map = L.map('map').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add marker
    marker = L.marker([lat, lng], {
        draggable: true
    }).addTo(map);

    // Add radius circle
    circle = L.circle([lat, lng], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);

    // Update coordinates when marker is dragged
    marker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        document.getElementById('latitude').value = position.lat.toFixed(6);
        document.getElementById('longitude').value = position.lng.toFixed(6);
        circle.setLatLng(position);
    });

    // Update marker and circle when map is clicked
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);

        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
    });
}

function updateMapFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        map.setView([lat, lng], map.getZoom());
    }
}

function updateCircle() {
    const radius = parseInt(document.getElementById('radius').value) || 100;
    circle.setRadius(radius);
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);

                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);

                // Show success message
                const toast = document.createElement('div');
                toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>Lokasi berhasil didapat!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 3000);
            },
            function(error) {
                let errorMsg = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Akses lokasi ditolak';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Timeout';
                        break;
                    default:
                        errorMsg += 'Error tidak diketahui';
                        break;
                }

                // Show error message
                const toast = document.createElement('div');
                toast.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>${errorMsg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 5000);
            }
        );
    } else {
        alert('Browser tidak mendukung geolocation');
    }
}

function searchLocation() {
    const query = prompt('Masukkan nama tempat atau alamat:');
    if (query) {
        // Simple geocoding using Nominatim
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);

                    marker.setLatLng([lat, lng]);
                    circle.setLatLng([lat, lng]);
                    map.setView([lat, lng], 16);

                    // Show success message
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                    toast.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>Lokasi "${data[0].display_name}" berhasil ditemukan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 5000);
                } else {
                    alert('Lokasi tidak ditemukan. Silakan coba dengan kata kunci yang berbeda.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mencari lokasi.');
            });
    }
}
</script>
@endpush
