{{-- resources/views/form-datetime.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold text-center mb-6">Monitoring Mutasi Berkas</h2>

        {{-- Form Pilih Tanggal --}}
        <form method="GET" class="flex justify-center mb-6">
            <input type="date" name="selected_date" value="{{ $selected_date }}"
                class="border rounded-l px-3 py-2 focus:outline-none focus:ring w-60">
            <button type="submit" class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700">
                Tampilkan
            </button>
        </form>

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Tanggal: {{ $selected_date }}</h3>
            <form method="POST" id="form-taksid" action="{{ route('monitoring.mutasi_berkas.repair') }}">
                @csrf
                <input type="hidden" name="selected_date" value="{{ $selected_date }}">
                <button type="submit" id="btn-taksid" class="bg-blue-600 text-white px-3 py-1 rounded mb-2">
                    Update ALL
                </button>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 border">No Rawat</th>
                        <th class="px-3 py-2 border">No RM</th>
                        <th class="px-3 py-2 border">Nama</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Registrasi</th>
                        <th class="px-3 py-2 border">Validasi</th>
                        <th class="px-3 py-2 border">Dikirim</th>
                        <th class="px-3 py-2 border">Diterima</th>
                        <th class="px-3 py-2 border">Jam Rawat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($results as $row)
                        @php
                            $row_class = '';
                            if ($row->dikirim && $row->diterima && $row->jam_rawat) {
                                $t_kirim = strtotime($row->dikirim);
                                $t_diterima = strtotime($row->diterima);
                                $t_rawat = strtotime(date('Y-m-d') . ' ' . $row->jam_rawat);

                                $row_class =
                                    $t_kirim < $t_diterima && $t_diterima < $t_rawat ? 'bg-green-100' : 'bg-red-100';
                            }
                        @endphp
                        <tr class="{{ $row_class }}">
                            <td class="px-3 py-2 border">{{ $row->no_rawat }}</td>
                            <td class="px-3 py-2 border">{{ $row->no_rkm_medis }}</td>
                            <td class="px-3 py-2 border">{{ $row->nm_pasien }}</td>
                            <td class="px-3 py-2 border">{{ $row->status_lanjut }}</td>
                            <td class="px-3 py-2 border">{{ $row->tgl_registrasi . ' ' . $row->jam_reg }}</td>
                            <td class="px-3 py-2 border">{{ $row->validasi }}</td>
                            <td class="px-3 py-2 border">{{ $row->dikirim }}</td>
                            <td class="px-3 py-2 border">{{ $row->diterima }}</td>
                            <td class="px-3 py-2 border">{{ $row->tgl_perawatan . ' ' . $row->jam_rawat }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-2 text-center text-gray-500">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
