@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Rekap Detil Tindakan</h3>

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
            <form action="{{ route('monitoring.klaim.export') }}" method="POST" class="flex">
                @csrf
                <input type="hidden" name="data" value="{{ json_encode($flattened) }}">
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 w-full sm:w-auto">
                    Export Excel
                </button>
            </form>
            <div class="overflow-x-auto border rounded-lg shadow">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border px-2 py-2 text-left font-semibold uppercase text-gray-700 w-12">
                                No
                            </th>
                            @foreach ($allKeys as $key)
                                <th class="border px-2 py-2 text-left font-semibold uppercase text-gray-700">
                                    {{ str_replace('_', ' ', str_replace('.', ' ', $key)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flattened as $index => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border px-2 py-1 text-center">
                                    {{ $index + 1 }}
                                </td>
                                @foreach ($allKeys as $key)
                                    <td class="border px-2 py-1">
                                        {{ $row[$key] ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        @else
            <div class="p-4 bg-yellow-100 text-yellow-800 rounded">
                Tidak ada data.
            </div>
        @endif
    </div>
@endsection
