@extends('layouts.app') 

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detail Pendaftaran</h2>

    <!-- Form Filter Tanggal -->
    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="tgl_awal" class="block text-sm font-medium text-gray-700">Tanggal Awal</label>
            <input required type="date" name="tgl_awal" id="tgl_awal"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ request('tgl_awal') }}">
        </div>

        <div>
            <label for="tgl_akhir" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
            <input required type="date" name="tgl_akhir" id="tgl_akhir"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ request('tgl_akhir') }}">
        </div>

        <div class="flex items-end">
            <button type="submit"
                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Filter
            </button>
        </div>
    </form>
    
   @if(request()->filled('tgl_awal'))
        <form action="{{route('nota_jalan.store_all')}}" method="post">
            @csrf
            <input type="hidden" name="tgl_awal" value="{{request('tgl_awal')}}">
            <input type="hidden" name="tgl_akhir" value="{{request('tgl_akhir')}}">
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Create Nota {{request('tgl_awal')}} - {{request('tgl_akhir')}}
            </button>
        </form>
        {{-- Tabel Data --}}
        @include('auto-billing.partials.table')
    @endif
</div>
@endsection
