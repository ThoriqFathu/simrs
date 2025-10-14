<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SIMRS')</title>
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


    <!-- Sidebar -->
    <aside class="bg-white h-screen shadow-lg overflow-y-auto py-5 transition-all duration-300 flex-shrink-0"
        :class="isOpen ? 'w-[300px]' : 'w-0 overflow-hidden'">
        <p class="text-2xl text-center font-bold">{{ get_name_rs() }}</p>
        <div class="bg-indigo-700 h-[1px] mt-4 mb-4"></div>

        <nav class="px-6">
            <ul class="my-2">
                {{-- <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="#" class="font-semibold">Dashboard</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('monitoring.antrol.index') }}" class="font-semibold">Antrol</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('monitoring.referensi_mjkn.index') }}" class="font-semibold">Referensi MJKN</a>
                </li> --}}
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('monitoring.klaim.index') }}" class="font-semibold">Klaim</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('jaspel.detil.index') }}" class="font-semibold">Detil Tindakan</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('monitoring.sinkron_sep.index') }}" class="font-semibold">Sinkron SEP</a>
                </li>
                <!-- Tambah menu lainnya sesuai kebutuhan -->
            </ul>
        </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1">
        <!-- Top bar -->
        <div class="bg-white h-[68px] shadow-lg px-4 flex justify-start items-center">
            <button @click="isOpen = !isOpen"
                class="p-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                <!-- Hamburger icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Content dari setiap halaman -->
        <div class="p-10">
            @yield('content')
        </div>
    </main>
</body>

</html>
