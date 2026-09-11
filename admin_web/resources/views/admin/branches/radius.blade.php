@extends('layouts.admin')

@section('title', 'Delivery Radius Configuration')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 mb-1">
                <i class="fa-solid fa-crosshairs"></i>
                <span>Hyperlocal Geo-Fencing Master</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Delivery Radius Configuration</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define hyperlocal delivery coverage boundaries in kilometers per operating store.</p>
        </div>
        <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Store Master</span>
        </a>
    </div>

    <!-- MAIN GRID: Controls & Live Map -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Radius Configuration Cards -->
        <div class="space-y-4">
            @foreach($branches as $branch)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $branch->name }}</h3>
                                <span class="text-[10px] font-mono text-slate-400">{{ $branch->code }} &bull; {{ $branch->status }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $branch->radius_km }} KM
                        </span>
                    </div>

                    <form action="{{ route('admin.branches.radius.update') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-1">
                                <span>Adjust Radius (KM)</span>
                                <span class="text-emerald-600 font-bold" id="radius_val_{{ $branch->id }}">{{ $branch->radius_km }} KM</span>
                            </div>
                            <input type="range" name="radius_km" min="0.5" max="25" step="0.5" value="{{ $branch->radius_km }}" 
                                oninput="document.getElementById('radius_val_{{ $branch->id }}').innerText = this.value + ' KM'; updateMapCircle({{ $branch->id }}, this.value);" 
                                class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                            <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                <span>0.5 KM</span>
                                <span>12 KM</span>
                                <span>25 KM</span>
                            </div>
                        </div>

                        <div class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-xl space-y-1">
                            <p><strong class="text-slate-700">Coordinates:</strong> {{ $branch->latitude }}, {{ $branch->longitude }}</p>
                            <p><strong class="text-slate-700">Estimated Area:</strong> ~{{ round(pi() * pow($branch->radius_km, 2), 1) }} sq. km</p>
                        </div>

                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-floppy-disk text-emerald-400"></i>
                            <span>Save {{ $branch->name }} Radius</span>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Right: Interactive Leaflet Live Visualizer -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-map text-emerald-600"></i>
                    <span>Live Interactive Geo-Radius Map</span>
                </h3>
                <span class="text-[11px] text-slate-400">Real-time circle updates on slider drag</span>
            </div>

            <div id="radiusMap" class="w-full h-[520px] rounded-xl border border-slate-200 overflow-hidden shadow-inner"></div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    let radiusMap;
    const branchCircles = {};

    document.addEventListener('DOMContentLoaded', function() {
        radiusMap = L.map('radiusMap').setView([17.6890, 74.0900], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(radiusMap);

        const branchesData = @json($branches);

        branchesData.forEach(b => {
            const marker = L.marker([b.latitude, b.longitude]).addTo(radiusMap);
            marker.bindPopup(`<b>${b.name} (${b.code})</b><br>${b.address}<br><b>Current Radius: ${b.radius_km} KM</b>`);

            const circle = L.circle([b.latitude, b.longitude], {
                color: b.id == 1 ? '#10b981' : '#3b82f6',
                fillColor: b.id == 1 ? '#10b981' : '#3b82f6',
                fillOpacity: 0.18,
                radius: b.radius_km * 1000
            }).addTo(radiusMap);

            branchCircles[b.id] = circle;
        });
    });

    function updateMapCircle(branchId, km) {
        if (branchCircles[branchId]) {
            branchCircles[branchId].setRadius(km * 1000);
        }
    }
</script>
@endpush
