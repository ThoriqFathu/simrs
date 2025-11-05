@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">
            Detail Pasien Penyakit {{ $kode }} — {{ $nm_penyakit }}
        </h1>
        <p class="text-gray-600 mb-6">
            Periode: {{ $tgl_awal }} s/d {{ $tgl_akhir }}
            @if ($umur_min || $umur_max)
                | Umur: {{ $umur_min ?? 0 }} - {{ $umur_max ?? 200 }} tahun
            @endif
        </p>

        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="border-b px-4 py-2 text-left font-semibold text-gray-700">No. Rawat</th>
                        <th class="border-b px-4 py-2 text-left font-semibold text-gray-700">Tgl Registrasi</th>
                        <th class="border-b px-4 py-2 text-left font-semibold text-gray-700">Nama Pasien</th>
                        <th class="border-b px-4 py-2 text-left font-semibold text-gray-700">Umur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pasien as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="border-b px-4 py-2">{{ $p->no_rawat }}</td>
                            <td class="border-b px-4 py-2">{{ $p->tgl_registrasi }}</td>
                            <td class="border-b px-4 py-2">{{ $p->nm_pasien }}</td>
                            <td class="border-b px-4 py-2">{{ $p->umurdaftar }} {{ $p->sttsumur }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">Tidak ada data pasien ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="{{ route('penyakit.index', ['tgl_awal' => $tgl_awal, 'tgl_akhir' => $tgl_akhir, 'umur_min' => $umur_min, 'umur_max' => $umur_max]) }}"
                class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                ← Kembali ke Rekap
            </a>
        </div>
    </div>
@endsection
