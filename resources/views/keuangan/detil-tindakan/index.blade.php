@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Rekap Detil Tindakan</h3>

        {{-- Filter Tanggal & Lainnya --}}
        <form class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" method="get">
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="w-full border rounded p-2" value="{{ $tanggalAwal }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="w-full border rounded p-2" value="{{ $tanggalAkhir }}">
            </div>
            {{-- Jaminan --}}
            <div>
                <label class="block mb-1 text-sm font-medium">Jaminan</label>
                <select name="jaminan" id="jaminan" class="w-full border rounded p-2">
                    <option value="umum" {{ $jaminan == 'umum' ? 'selected' : '' }}>Umum</option>
                    <option value="bpjs" {{ $jaminan == 'bpjs' ? 'selected' : '' }}>BPJS</option>
                    {{-- <option value="lainnya" {{ $jaminan == 'lainnya' ? 'selected' : '' }}>Lainnya</option> --}}
                </select>
            </div>

            {{-- Jenis Pelayanan --}}
            <div id="jenisPelayananWrapper">
                <label class="block mb-1 text-sm font-medium">Jenis Pelayanan</label>
                <select name="jns" id="jenisPelayanan" class="w-full border rounded p-2">
                    <option value="1" {{ $jnsPelayanan == 1 ? 'selected' : '' }}>Rawat Inap</option>
                    <option value="2" {{ $jnsPelayanan == 2 ? 'selected' : '' }}>Rawat Jalan</option>
                    <option value="3" {{ $jnsPelayanan == 3 ? 'selected' : '' }}>IGD</option>
                </select>
            </div>



            {{-- Status Bayar --}}
            <div id="statusBayarWrapper">
                <label class="block mb-1 text-sm font-medium">Status Bayar</label>
                <select name="status_bayar" id="statusBayar" class="w-full border rounded p-2">
                    <option value="Sudah Bayar" {{ $status_bayar == 'Sudah Bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                    <option value="Belum Bayar" {{ $status_bayar == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                </select>
            </div>

            {{-- Tombol Submit --}}
            <div class="col-span-1 md:col-span-5">
                <button type="submit"
                    class="w-full md:w-auto px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Tampilkan
                </button>
            </div>
        </form>

        @if ($filepath)
            <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0 mb-6">
                <form action="{{ route('export.tindakan') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="filepath" value="{{ $filepath }}">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        Export Excel
                    </button>
                </form>

                <form action="{{ route('export.tindakan.csv') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="filepath" value="{{ $filepath }}">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
                        Export CSV
                    </button>
                </form>
            </div>

            <div class="p-3 bg-gray-50 border rounded text-gray-700">
                <span class="font-semibold">Data Siap di export!</span>
            </div>
        @endif
    </div>

    {{-- Script untuk sembunyikan field --}}
    <script>
        function toggleFields() {
            const jaminan = document.getElementById('jaminan').value;
            const jenisPelayanan = document.getElementById('jenisPelayananWrapper');
            const statusBayar = document.getElementById('statusBayarWrapper');

            if (jaminan === 'umum') {
                jenisPelayanan.style.display = 'none';
                statusBayar.style.display = 'block';
            } else if (jaminan === 'bpjs') {
                jenisPelayanan.style.display = 'block';
                statusBayar.style.display = 'none';
            } else {
                jenisPelayanan.style.display = 'block';
                statusBayar.style.display = 'block';
            }
        }

        document.getElementById('jaminan').addEventListener('change', toggleFields);
        // Panggil sekali di awal biar tampilannya sesuai dengan nilai default
        toggleFields();
    </script>
@endsection
