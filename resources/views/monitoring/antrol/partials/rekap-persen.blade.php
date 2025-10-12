@php
    $total_kunjungan = count($data_kunjungan_sep) - $countSumberDataSelesai['Batal'];
    $total_all_selesai = $countSumberDataSelesai['MJKN'] + $countSumberDataSelesai['Non_MJKN'];

    if ($total_kunjungan > 0) {
        $persen_all_antrol = number_format(($total_all_selesai / $total_kunjungan) * 100, 2);
        $persen_mjkn = number_format(($countSumberDataSelesai['MJKN'] / $total_kunjungan) * 100, 2);
    } else {
        $persen_all_antrol = '0.00';
        $persen_mjkn = '0.00';
    }
@endphp

{{-- Table untuk jumlah SEP per poli --}}
<div class="bg-gray-100 text-gray-800 px-4 py-3 rounded shadow lg:col-span-1">
    <div class="text-sm font-semibold mb-2">Jumlah SEP per Poli</div>
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 text-sm text-left">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-3 py-2">Poli</th>
                    <th class="border px-3 py-2">Jumlah SEP</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($countSepPoli as $key => $val)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-3 py-2">{{ $key }}</td>
                        <td class="border px-3 py-2 font-bold">{{ $val }}</td>
                    </tr>
                @endforeach
                <tr class="bg-red-400 hover:bg-red-300">
                    <td class="border px-3 py-2">Batal</td>
                    <td class="border px-3 py-2 font-bold">{{ $countSumberDataSelesai['Batal'] }}</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<div class="mb-2 grid grid-cols-1 sm:grid-cols-2 gap-4">

    <div class="bg-blue-100 text-blue-800 px-4 py-3 rounded shadow">
        <div class="text-sm font-semibold">Pemanfaatan Antrol</div>
        <div class="text-lg font-bold">
            {{ $persen_all_antrol }}% ({{ $total_all_selesai }} dari {{ $total_kunjungan }})
        </div>
    </div>

    <div class="bg-green-100 text-green-800 px-4 py-3 rounded shadow">
        <div class="text-sm font-semibold">MJKN</div>
        <div class="text-lg font-bold">
            {{ $persen_mjkn }}% ({{ $countSumberDataSelesai['MJKN'] }} dari {{ $total_kunjungan }})
        </div>
    </div>
</div>
