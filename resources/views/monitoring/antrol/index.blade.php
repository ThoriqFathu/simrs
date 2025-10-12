@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">Dashboard Monitoring Antrian
            {{ request('tanggal_awal', $tanggal_awal ?? now()->format('Y-m-d')) }} -
            {{ request('tanggal_akhir', $tanggal_akhir ?? now()->format('Y-m-d')) }}</h1>
        @include('monitoring.antrol.partials.rekap-persen')
        {{-- Form filter (tidak pakai collapse) --}}
        <div class="mb-4">
            @include('monitoring.antrol.partials.form-filter')
        </div>

        <div x-data="{
            openSepGagal: true,
            openSepBelum: true,
            openAntrol: true
        }">
            @if (count($data_sep_gagal) > 0)
                {{-- Table SEP gagal --}}
                <div class="mb-4">
                    <button @click="openSepGagal = !openSepGagal"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow transition mb-2">
                        <span x-text="openSepGagal ? 'Hide Data Sep Gagal' : 'Show Data Sep Gagal'"></span>
                    </button>
                    <div x-show="openSepGagal" x-transition>
                        @include('monitoring.antrol.partials.data-sep-gagal')
                    </div>
                </div>
            @endif

            @if (count($data_sep_belum_selesai) > 0)
                {{-- Table SEP Belum Selesai --}}
                <div class="mb-4">
                    <button @click="openSepBelum = !openSepBelum"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white font-semibold shadow transition mb-2">
                        <span x-text="openSepBelum ? 'Hide Data SEP Belum Selesai' : 'Show Data SEP Belum Selesai'"></span>
                    </button>
                    <div x-show="openSepBelum" x-transition>
                        @include('monitoring.antrol.partials.data-sep-belum-selesai')
                    </div>
                </div>
            @endif



            {{-- Table Data Antrol --}}
            <div class="mb-4">
                <button @click="openAntrol = !openAntrol"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow transition mb-2">
                    <span x-text="openAntrol ? 'Hide Data Antrol' : 'Show Data Antrol'"></span>
                </button>
                <div x-show="openAntrol" x-transition>
                    @include('monitoring.antrol.partials.data-antrol')
                </div>
            </div>
        </div>
    </div>
@endsection
