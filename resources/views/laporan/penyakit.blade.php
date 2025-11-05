@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Laporan Jumlah Pasien per Kode Penyakit</h1>

        {{-- 🔍 Form Filter --}}
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8 bg-white p-4 rounded-lg shadow">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
                <input type="date" name="tgl_awal" value="{{ $tgl_awal }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" value="{{ $tgl_akhir }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Penyakit (Opsional)</label>
                <input type="text" name="kd_penyakit" value="{{ $kd_penyakit }}" placeholder="Contoh: A09"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Umur Minimal (Tahun)</label>
                <input type="number" name="umur_min" value="{{ $umur_min }}" min="0"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Umur Maksimal (Tahun)</label>
                <input type="number" name="umur_max" value="{{ $umur_max }}" min="0"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow">
                    Tampilkan
                </button>
            </div>
        </form>


        {{-- 📊 Tabel Data --}}
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="border-b px-4 py-2 text-left text-gray-700 font-semibold">Kode Penyakit</th>
                        <th class="border-b px-4 py-2 text-left text-gray-700 font-semibold">Nama Penyakit</th>
                        <th class="border-b px-4 py-2 text-right text-gray-700 font-semibold">Total Pasien</th>
                        <th class="border-b px-4 py-2 text-right text-gray-700 font-semibold">Aksi</th>

                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="border-b px-4 py-2">{{ $row->kd_penyakit }}</td>
                            <td class="border-b px-4 py-2">{{ $row->nm_penyakit }}</td>
                            <td class="border-b px-4 py-2 text-right font-semibold">{{ $row->total_pasien }}</td>
                            <td class="border-b px-4 py-2 text-right">
                                <a href="{{ route('penyakit.detail', ['kode' => $row->kd_penyakit, 'tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir, 'umur_min' => $umur_min, 'umur_max' => $umur_max]) }}"
                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                    Lihat Detail →
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">Tidak ada data ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
