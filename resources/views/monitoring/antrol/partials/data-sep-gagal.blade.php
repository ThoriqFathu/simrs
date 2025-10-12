<h3 class="text-lg font-bold mb-2">Data Sep Gagal Masuk Antrol</h3>
<div class="overflow-x-auto">
    <table class="min-w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">NO</th>
                @if (!empty($data_sep_gagal))
                    @foreach (array_keys($data_sep_gagal[0]) as $key)
                        <th class="border px-3 py-2">{{ ucfirst($key) }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data_sep_gagal as $index => $item)
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
