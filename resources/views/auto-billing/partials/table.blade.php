<table class="table-auto w-full border border-gray-200 text-sm">
    <thead>
        <tr>
            <th class="font-medium text-gray-600 py-2">No. Rawat</th>
            <th class="font-medium text-gray-600 py-2">Tanggal Registrasi</th>
            <th class="font-medium text-gray-600 py-2">Kode Dokter</th>
            <th class="font-medium text-gray-600 py-2">No. Rekam Medis</th>
            <th class="font-medium text-gray-600 py-2">Poli</th>
            <th class="font-medium text-gray-600 py-2">Status Lanjut</th>
            <th class="font-medium text-gray-600 py-2">Jenis Pembayaran</th>
            <th class="font-medium text-gray-600 py-2">Status Bayar</th>
            <th class="font-medium text-gray-600 py-2">Nota</th>

        </tr>


        
    </thead>
    
    <tbody class="divide-y divide-gray-100">
        @forelse ($list_data as $data)
            <tr>           
                <td class="py-2">{{ $data->no_rawat }}</td>                
                <td class="py-2">{{ $data->tgl_registrasi }} {{ $data->jam_reg }}</td>                
                <td class="py-2">{{ $data->kd_dokter }}</td>                
                <td class="py-2">{{ $data->no_rkm_medis }}</td>                
                <td class="py-2">{{ $data->kd_poli }}</td>                            
                <td class="py-2">{{ $data->status_lanjut }}</td>                
                <td class="py-2">{{ $data->kd_pj }}</td>                         
                <td class="py-2">{{ $data->status_bayar }}</td> 
                
                @php
                    $is_nota = App\Models\NotaJalan::find($data->no_rawat);
                @endphp
                <td class="py-2">{{ $is_nota !== null ? 'Sudah' : 'Belum'}}</td> 
            </tr>
        @empty
            <tr>
                <td colspan="7">Tidak Ada Data</td>
            </tr>
        @endforelse
        
    </tbody>
</table>