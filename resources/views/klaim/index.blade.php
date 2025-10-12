@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Monitoring Klaim BPJS</h3>

        <form class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" method="get">
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Pulang Awal</label>
                <input type="date" name="tanggal_awal" class="w-full border rounded p-2" value="{{ $tanggalAwal }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Tanggal Pulang Akhir</label>
                <input type="date" name="tanggal_akhir" class="w-full border rounded p-2" value="{{ $tanggalAkhir }}">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Jenis Pelayanan</label>
                <select name="jns" class="w-full border rounded p-2">
                    <option value="1" {{ $jnsPelayanan == 1 ? 'selected' : '' }}>Inap</option>
                    <option value="2" {{ $jnsPelayanan == 2 ? 'selected' : '' }}>Jalan</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Status Klaim</label>
                <select name="status" class="w-full border rounded p-2">
                    <option value="1" {{ $statusKlaim == 1 ? 'selected' : '' }}>Proses Verifikasi</option>
                    <option value="2" {{ $statusKlaim == 2 ? 'selected' : '' }}>Pending Verifikasi</option>
                    <option value="3" {{ $statusKlaim == 3 ? 'selected' : '' }}>Klaim</option>
                </select>
            </div>
            <div class="col-span-1 md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Tampilkan
                </button>
            </div>
        </form>


        @if ($httpcode != 200)
            <div class="p-4 bg-red-100 text-red-800 rounded">
                Gagal mengambil data API. HTTP Code: {{ $httpcode }}
            </div>
        @else
            @if (!empty($flattened))
                <div class="flex flex-col sm:flex-row gap-2 mb-4">
                    {{-- <form action="{{ route('monitoring.klaim.mode_copy') }}" method="GET" class="flex">
                        <input type="hidden" value="{{ json_encode($flattened) }}" name="data">
                        <button type="submit"
                            class="px-4 py-2 bg-lime-600 text-white rounded hover:bg-lime-700 w-full sm:w-auto">
                            Mode Copy
                        </button>
                    </form> --}}

                    <form action="{{ route('monitoring.klaim.export') }}" method="POST" class="flex">
                        @csrf
                        <input type="hidden" name="data" value="{{ json_encode($flattened) }}">
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 w-full sm:w-auto">
                            Export Excel
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border text-sm">
                        <thead>
                            <tr class="bg-gray-200">
                                @foreach ($allKeys as $key)
                                    <th class="border px-2 py-1">
                                        {{ strtoupper(str_replace('_', ' ', str_replace('.', ' ', $key))) }}
                                    </th>
                                @endforeach

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($flattened_dot as $row)
                                <tr class="{{ empty($row['no_rawat']) ? 'bg-red-100' : 'bg-green-100' }}">
                                    @foreach ($allKeys as $key)
                                        <td class="border px-2 py-1">
                                            {{ $row[$key] ?? ($key != 'no_rawat' ? 0 : '') }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 bg-yellow-100 text-yellow-800 rounded">Tidak ada data klaim.</div>
            @endif
        @endif
    </div>
@endsection
