@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Monitoring Sinkron SEP BPJS</h3>

        {{-- Filter Tanggal --}}
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" method="get">
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="w-full border rounded p-2" value="{{ $tanggalAwal }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="w-full border rounded p-2" value="{{ $tanggalAkhir }}">
            </div>

            <div class="col-span-1 md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Tampilkan
                </button>
            </div>
        </form>

        {{-- Tabel Data --}}
        @if (!empty($flattened))
            <div class="overflow-x-auto border rounded-lg shadow">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            @foreach ($allKeys as $key)
                                <th class="border px-2 py-2 text-left font-semibold uppercase text-gray-700">
                                    {{ str_replace('_', ' ', str_replace('.', ' ', $key)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flattened as $row)
                            <tr class="{{ $row['sinkron'] == 0 ? 'bg-red-100' : 'bg-green-100' }} hover:bg-gray-50">
                                @foreach ($allKeys as $key)
                                    <td class="border px-2 py-1">
                                        @if ($key === 'jnspelayanan')
                                            {{ $row[$key] == 2 ? 'Ralan' : ($row[$key] == 1 ? 'Ranap' : '-') }}
                                        @else
                                            {{ $row[$key] ?? ($key != 'nama' ? '0' : '') }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $data->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-4 bg-yellow-100 text-yellow-800 rounded">
                Tidak ada data.
            </div>
        @endif
    </div>
@endsection
