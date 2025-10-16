@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Rekap Detil Tindakan</h3>

        {{-- Filter Tanggal --}}
        <form id="form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" method="get">
            {{-- <form class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" method="get"> --}}
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="w-full border rounded p-2" value="{{ $tanggalAwal }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="w-full border rounded p-2" value="{{ $tanggalAkhir }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Jenis Pelayanan</label>
                <select name="jns" class="w-full border rounded p-2">
                    <option value="1" {{ $jnsPelayanan == 1 ? 'selected' : '' }}>Rawat Inap</option>
                    <option value="2" {{ $jnsPelayanan == 2 ? 'selected' : '' }}>Rawat Jalan</option>
                    <option value="3" {{ $jnsPelayanan == 3 ? 'selected' : '' }}>IGD</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Jaminan</label>
                <select name="jaminan" class="w-full border rounded p-2">
                    <option value="umum" {{ $jaminan == 'umum' ? 'selected' : '' }}>Umum</option>
                    <option value="bpjs" {{ $jaminan == 'bpjs' ? 'selected' : '' }}>BPJS</option>
                    <option value="lainnya" {{ $jaminan == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Status Bayar</label>
                <select name="status_bayar" class="w-full border rounded p-2">
                    <option value="Sudah Bayar" {{ $status_bayar == 'Sudah Bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                    <option value="Belum Bayar" {{ $status_bayar == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                </select>
            </div>

            <div class="col-span-1 md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Tampilkan
                </button>
            </div>
        </form>

        {{-- Info Loading / Total Data --}}
        <div id="loading" class="p-3 text-gray-600">Silakan filter dan klik "Tampilkan"…</div>

        {{-- Tombol Export --}}
        <div id="export-container" class="hidden my-4">
            <button type="button" onclick="exportExcel()"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                Export Excel
            </button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        let offset = 0;
        const limit = 1000;
        let allData = [];

        async function fetchBatch(offset) {
            const params = new URLSearchParams({
                offset,
                limit,
                tanggal_awal: document.querySelector('[name="tanggal_awal"]').value,
                tanggal_akhir: document.querySelector('[name="tanggal_akhir"]').value,
                jns: document.querySelector('[name="jns"]').value,
                jaminan: document.querySelector('[name="jaminan"]').value,
                status_bayar: document.querySelector('[name="status_bayar"]').value,
            });

            const res = await fetch(`{{ route('detil-tindakan.data') }}?${params}`);
            if (!res.ok) throw new Error('Gagal mengambil data');
            return res.json();
        }

        async function loadData() {
            document.getElementById('loading').innerText = '⏳ Sedang memuat data...';
            let done = false;

            while (!done) {
                const result = await fetchBatch(offset);
                allData = allData.concat(result.data);
                offset = result.offset;
                done = result.done;
            }

            document.getElementById('loading').innerText =
                `✅ Data berhasil dimuat: ${allData.length.toLocaleString()} baris`;
            document.getElementById('export-container').classList.remove('hidden');
        }

        document.querySelector('#form-data').addEventListener('submit', function(e) {
            e.preventDefault();
            offset = 0;
            allData = [];
            document.getElementById('export-container').classList.add('hidden');
            loadData();
        });

        function exportExcel() {
            if (allData.length === 0) {
                alert('Belum ada data untuk diexport');
                return;
            }

            const ws = XLSX.utils.json_to_sheet(allData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data");

            const fileName = `rekap_tindakan_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>
@endsection
