@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">📊 Laporan Kasir</h2>

        <form method="get" action="{{ route('laporan.kasir') }}"
            class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-6 bg-white p-4 rounded-lg shadow">
            <div>
                <label for="tgl1" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" id="tgl1" name="tgl1"
                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    value="{{ request('tgl1', $tgl1 ?? date('Y-m-d')) }}">
            </div>
            <div>
                <label for="tgl2" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                <input type="date" id="tgl2" name="tgl2"
                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    value="{{ request('tgl2', $tgl2 ?? date('Y-m-d')) }}">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    🔍 Tampilkan
                </button>
                @if (!empty($rows))
                    <a href="{{ route('laporan.kasir.export', ['tgl1' => request('tgl1', $tgl1 ?? ''), 'tgl2' => request('tgl2', $tgl2 ?? '')]) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        📁 Export XLSX
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full text-sm text-gray-800">
                <thead class="bg-blue-50 border-b text-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left">No</th>
                        <th class="px-4 py-2 text-left">Tanggal Bayar</th>
                        <th class="px-4 py-2 text-left">No Rawat</th>
                        <th class="px-4 py-2 text-left">No Nota</th>
                        <th class="px-4 py-2 text-left">Nama Pasien</th>
                        <th class="px-4 py-2 text-right">Jumlah Bayar</th>
                        <th class="px-4 py-2 text-left">Petugas</th>
                        @foreach ($akunbayar as $a)
                            <th class="px-4 py-2 text-right whitespace-nowrap">{{ $a->nm_rek }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                        $grandTotal = 0;
                    @endphp
                    @forelse ($rows as $r)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $no++ }}</td>
                            <td class="px-4 py-2">{{ $r['tgl_bayar'] }}</td>
                            <td class="px-4 py-2">{{ $r['no_rawat'] }}</td>
                            <td class="px-4 py-2">{{ $r['no_nota'] }}</td>
                            <td class="px-4 py-2">{{ $r['nama_pasien'] }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($r['jumlah_bayar'], 0, ',', '.') }}</td>
                            <td class="px-4 py-2">{{ $r['petugas'] }}</td>
                            @foreach ($akunbayar as $a)
                                <td class="px-4 py-2 text-right">
                                    {{ number_format($r['detail'][$a->kd_rek] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        @php $grandTotal += $r['jumlah_bayar']; @endphp
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($akunbayar) }}" class="text-center py-4 text-gray-500">
                                Tidak ada data untuk rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-semibold text-gray-800 border-t">
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right">TOTAL</td>
                        <td class="px-4 py-2 text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        <td></td>
                        @foreach ($akunbayar as $a)
                            <td class="px-4 py-2 text-right">
                                {{ number_format($totalPerAkun[$a->kd_rek] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
