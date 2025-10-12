<h3 class="text-lg font-bold mb-2">Data Sep Belum Selesai Dilayani</h3>
<form method="POST" id="form-taksid" action="{{ route('monitoring.antrol.send_taskid') }}">
    @csrf

    <input type="hidden" name="data_sep_belum_selesai" value="{{ json_encode($data_sep_belum_selesai) }}">
    @if (!empty($data_sep_belum_selesai))
        <button type="submit" id="btn-taksid" class="bg-blue-600 text-white px-3 py-1 rounded mb-2">
            Kirim Taks Id
        </button>
        <script>
            document.getElementById('form-taksid').addEventListener('submit', function(e) {
                e.preventDefault(); // hentikan submit default

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin mengirim Taks Id?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                });
            });
        </script>
    @endif
</form>

<div class="overflow-x-auto">
    <table class="min-w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">NO</th>
                @if (!empty($data_sep_belum_selesai))
                    @foreach (array_keys($data_sep_belum_selesai[0]) as $key)
                        <th class="border px-3 py-2">{{ ucfirst($key) }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data_sep_belum_selesai as $index => $item)
                <tr class="even:bg-gray-50 hover:bg-gray-100">
                    <td class="border px-3 py-2">{{ $index + 1 }}</td>
                    @foreach ($item as $value)
                        <td class="border px-3 py-2 break-words">
                            {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="999" class="text-center py-2">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
