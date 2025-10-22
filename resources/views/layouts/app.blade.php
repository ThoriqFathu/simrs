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
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Selesai!',
                text: {!! json_encode(session('success')) !!},
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
<body class="flex h-screen overflow-hidden" x-data="{ isOpen: true }">
    <!-- Sidebar -->
    <aside class="bg-white h-screen fixed left-0 top-0 shadow-lg overflow-y-auto py-5 transition-all duration-300"
        :class="isOpen ? 'w-[300px]' : 'w-0 overflow-hidden'">
        <p class="text-2xl text-center font-bold">{{ get_name_rs() }}</p>
        <div class="bg-indigo-700 h-[1px] mt-4 mb-4"></div>

        <nav class="px-6">
            @include('layouts.partials.sidebar-menu')
        </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col transition-all duration-300"
        :class="isOpen ? 'ml-[300px]' : 'ml-0'">
        <!-- Top bar -->
        <header class="bg-white h-[68px] shadow-lg px-4 flex justify-start items-center fixed top-0 right-0 transition-all duration-300"
            :class="isOpen ? 'left-[300px]' : 'left-0'">
            <button @click="isOpen = !isOpen"
                class="p-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">
                <!-- Hamburger icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </header>

        <!-- Scrollable content -->
        <main class="flex-1 mt-[68px] overflow-y-auto h-[calc(100vh-68px)] p-10 bg-gray-50">
            @yield('content')
        </main>
    </div>
</body>


</html>
