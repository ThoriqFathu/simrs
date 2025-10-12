<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'IT RSIA HS')</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Font Awesome Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex" x-data="{ isOpen: true }">
    {{-- SweetAlert jika ada session --}}
    @if (session('status'))
        <script>
            Swal.fire({
                title: 'Selesai!',
                text: {!! json_encode(session('status')) !!},
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    @if (session('status_update_waktu'))
        <script>
            Swal.fire({
                title: 'Selesai!',
                text: JSON.stringify(@json(session('status')), null, 2), // null, 2 → untuk indentasi
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif


    <!-- Main content -->
    <main class="flex-1">


        <!-- Content dari setiap halaman -->
        <div class="p-10">
            <div class="max-w-7xl mx-auto py-8 px-4">



                @if (!empty($flattened))
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
                                @foreach ($flattened as $row)
                                    <tr class="{{ empty($row['no_rawat']) ? 'bg-red-100' : 'bg-green-100' }}">
                                        @foreach ($allKeys as $key)
                                            <td class="border px-2 py-1">
                                                @php
                                                    // Ambil value sesuai key
                                                    $value = $row[$key] ?? ($key != 'no_rawat' ? 0 : '');

                                                    // Jika key peserta.noKartu tambahkan ' di depannya supaya Excel baca sebagai text
if ($key === 'peserta.noKartu') {
    $value = "'" . $value;
                                                    }
                                                @endphp

                                                {{ $value }}
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
            </div>
        </div>
    </main>
</body>

</html>
