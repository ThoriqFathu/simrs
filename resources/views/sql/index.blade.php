@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">SQL Query Exporter (Lokal)</h1>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="get" action="{{ route('sql.index') }}" class="space-y-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan SQL Query</label>
            <textarea name="sql" rows="6" class="w-full p-3 border rounded font-mono text-sm"
                placeholder="Contoh: SELECT id, name, email FROM users LIMIT 100">{{ old('sql', $sql) }}</textarea>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Jalankan Query
                </button>
        </form>
        @if ($sql)
            <form action="{{ route('sql.export') }}" method="POST" target="_blank" class="inline">
                @csrf
                <input type="hidden" name="sql" value="{{ $sql }}">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Export ke Excel
                </button>
            </form>
        @endif

    </div>


    @if ($results->count())
        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        @if ($results->isNotEmpty())
                            @foreach (array_keys($results->first()) as $col)
                                <th class="px-4 py-2 text-left font-medium text-gray-600">{{ $col }}</th>
                            @endforeach
                        @else
                            <th class="px-4 py-2 text-left font-medium text-gray-600 text-red-500">
                                Tidak ada data
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($results as $row)
                        <tr>
                            @foreach ($row as $val)
                                <td class="border px-3 py-2">
                                    {{ is_array($val) || is_object($val) ? json_encode($val) : $val }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <p class="text-gray-500 text-xs mt-2">
            Menampilkan {{ $results->count() }} baris (batasan: hasil query penuh diekspor saat klik "Export").
        </p>
    @endif
    </div>
@endsection
