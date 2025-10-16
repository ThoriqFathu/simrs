{{-- resources/views/form-datetime.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-semibold mb-4">Form Tanggal & Waktu</h1>
            <p class="text-sm text-gray-500 mb-6">Isi form berikut lalu kirim. Field datetime menggunakan input
                <code>text</code>.
            </p>

            <form method="POST" action="{{ route('monitoring.antrol.manual_send_taskid') }}" class="space-y-6" novalidate>
                @csrf

                {{-- Text input --}}
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Kode Booking</label>
                    <input readonly id="kd_booking" name="kd_booking" type="text" value="{{ json_decode($kd_booking) }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('kd_booking')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Datetime 1 --}}
                <div>
                    <label for="taksid_3" class="block text-sm font-medium text-gray-700 mb-1">Taks Id 3</label>
                    <input id="taksid_3" name="taksid_3" type="text" value="{{ old('taksid_3') }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('taksid_3')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="taksid_4" class="block text-sm font-medium text-gray-700 mb-1">Taks Id 4</label>
                    <input id="taksid_4" name="taksid_4" type="text" value="{{ old('taksid_4') }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('taksid_4')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="taksid_5" class="block text-sm font-medium text-gray-700 mb-1">Taks Id 5</label>
                    <input id="taksid_5" name="taksid_5" type="text" value="{{ old('taksid_5') }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('taksid_5')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>



                {{-- Buttons --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Kirim
                    </button>

                    <button type="button" id="reset-btn"
                        class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-200">
                        Reset
                    </button>

                    <p id="preview" class="ml-auto text-sm text-gray-600"></p>
                </div>
            </form>

            {{-- Hasil Update --}}
            @if (session('updates'))
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Hasil Update:</h3>
                    @foreach (session('updates') as $taskid => $result)
                        @php
                            $isOk =
                                isset($result['response']['metadata']['code']) &&
                                $result['response']['metadata']['code'] == 200;
                            $message = $result['response']['metadata']['message'] ?? $result['response'];
                        @endphp
                        <div
                            class="p-3 mb-2 rounded {{ $isOk ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <strong>Task ID {{ $taskid }}:</strong>
                            {{ $isOk ? '✅ ' : '❌ ' }} {{ $message }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        // Optional: contoh preview singkat ketika user pilih datetime
        (function() {
            const mk = (id) => document.getElementById(id);
            const preview = mk('preview');
            const fields = ['taksid_3', 'taksid_4', 'taksid_5'];

            function updatePreview() {
                const vals = fields.map(f => {
                    const el = mk(f);
                    return el && el.value ? el.value.replace('T', ' ') : '';
                }).filter(Boolean);

                preview.textContent = vals.length ? `Preview: ${vals.join(' | ')}` : '';
            }

            fields.forEach(f => {
                const el = mk(f);
                if (!el) return;
                el.addEventListener('change', updatePreview);
                el.addEventListener('input', updatePreview);
            });

            mk('reset-btn').addEventListener('click', () => {
                document.querySelector('form').reset();
                updatePreview();
            });

            // inisialisasi preview
            updatePreview();
        })();
    </script>
@endsection
