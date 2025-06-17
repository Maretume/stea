@extends('layouts.app')

@section('title', 'Edit Kantor')
@section('page-title', 'Edit Kantor - ' . $office->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Edit Kantor</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('offices.update', $office) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Office Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kantor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $office->name) }}" 
                               placeholder="Contoh: Kantor Pusat Jakarta" required maxlength="100">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location Coordinates -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" 
                                   id="latitude" name="latitude" value="{{ old('latitude', $office->latitude) }}" 
                                   placeholder="-6.200000" required>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Koordinat lintang (-90 sampai 90)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" 
                                   id="longitude" name="longitude" value="{{ old('longitude', $office->longitude) }}" 
                                   placeholder="106.816666" required>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Koordinat bujur (-180 sampai 180)</small>
                        </div>
                    </div>

                    <!-- Radius -->
                    <div class="mb-3">
                        <label for="radius" class="form-label">Radius Absensi (meter) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('radius') is-invalid @enderror" 
                               id="radius" name="radius" value="{{ old('radius', $office->radius) }}" 
                               min="10" max="1000" placeholder="100" required>
                        @error('radius')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jarak maksimal untuk absensi (10-1000 meter)</small>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $office->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Kantor Aktif
                            </label>
                        </div>
                        <small class="text-muted">Centang jika kantor masih aktif digunakan</small>
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

                    <!-- Address (Optional) -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat (Opsional)</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" 
                                  placeholder="Alamat lengkap kantor...">{{ old('address', $office->address ?? '') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('offices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <div>
                            <a href="{{ route('offices.show', $office) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Kantor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Update map when coordinates change
    document.getElementById('latitude').addEventListener('input', updateMapFromInputs);
    document.getElementById('longitude').addEventListener('input', updateMapFromInputs);
    document.getElementById('radius').addEventListener('input', updateRadius);
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
        updateRadius();
    });
    
    // Update coordinates when map is clicked
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    });
}

function updateMapFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    
    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        map.setView([lat, lng]);
    }
}

function updateRadius() {
    const radius = parseInt(document.getElementById('radius').value) || 100;
    circle.setRadius(radius);
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
        }, function(error) {
            alert('Gagal mendapatkan lokasi: ' + error.message);
        });
    } else {
        alert('Geolocation tidak didukung oleh browser ini.');
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
                    
                    // Update address if found
                    if (data[0].display_name) {
                        document.getElementById('address').value = data[0].display_name;
                    }
                } else {
                    alert('Lokasi tidak ditemukan.');
                }
            })
            .catch(error => {
                alert('Gagal mencari lokasi: ' + error.message);
            });
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const latitude = parseFloat(document.getElementById('latitude').value);
    const longitude = parseFloat(document.getElementById('longitude').value);
    const radius = parseInt(document.getElementById('radius').value);
    
    if (!name) {
        e.preventDefault();
        alert('Nama kantor harus diisi!');
        return;
    }
    
    if (isNaN(latitude) || latitude < -90 || latitude > 90) {
        e.preventDefault();
        alert('Latitude harus berupa angka antara -90 dan 90!');
        return;
    }
    
    if (isNaN(longitude) || longitude < -180 || longitude > 180) {
        e.preventDefault();
        alert('Longitude harus berupa angka antara -180 dan 180!');
        return;
    }
    
    if (isNaN(radius) || radius < 10 || radius > 1000) {
        e.preventDefault();
        alert('Radius harus berupa angka antara 10 dan 1000 meter!');
        return;
    }
});
</script>
@endpush
