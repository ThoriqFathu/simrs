<ul class="my-2">
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="#" class="font-semibold">Dashboard</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('monitoring.mutasi_berkas.index') }}" class="font-semibold">Mutasi Berkas</a>
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
                </li>
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
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <a href="{{ route('sirs.kamar.index') }}" class="font-semibold">SIRS</a>
                </li>
                <li class="hover:bg-indigo-700 py-3 px-4 rounded hover:text-white flex items-center gap-2">
                    <img width="20" height="20"
                        src="https://img.icons8.com/material-rounded/100/dashboard-layout.png" alt="dashboard" />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Logout</button>
                    </form>
                </li>
                <!-- Tambah menu lainnya sesuai kebutuhan -->
            </ul>