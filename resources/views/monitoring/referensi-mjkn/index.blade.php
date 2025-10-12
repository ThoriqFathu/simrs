@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h3 class="text-xl font-bold mb-6">Monitoring Referensi MJKN BPJS</h3>
        @include('monitoring.referensi-mjkn.partials.form-filter')

        @if (!empty($data_ref_mjkn_array))
            <div class="overflow-x-auto">
                <table class="min-w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border px-3 py-2">No.</th>
                            @foreach (array_keys($data_ref_mjkn_array[0]) as $key)
                                <th class="border px-3 py-2">{{ ucfirst($key) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_ref_mjkn_array as $index => $item)
                            <tr class="even:bg-gray-50 hover:bg-gray-100">
                                <td class="border px-3 py-2">{{ $index + 1 }}</td>

                                @foreach ($item as $key => $value)
                                    <td class="border px-3 py-2 break-words text-center">
                                        @if ($key === 'match_antrol')
                                            @if ($value)
                                                <span class="text-green-600 font-semibold">Sukses</span>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('monitoring.referensi_mjkn.destroy') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="nobooking" value="{{ $item['nobooking'] }}">
                                                    <button type="submit"
                                                        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                                                        onclick="confirm('Yakin ingin hapus?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 bg-yellow-100 text-yellow-800 rounded">Tidak ada data.</div>
        @endif
    </div>
@endsection
