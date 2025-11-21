@php
    $path = explode('/', Request::path());
    $role = auth()->user()->role;
@endphp
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header justify-content-center">
            <a class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ asset('logodark.PNG') }}" style="width: 170px; height: 140px; margin-top: 2px" alt=""
                    class="logo logo-lg" />
                <img src="{{ asset('logo_favicon.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            {{-- <pre>{{ print_r(array_keys(session('hak_akses')->toArray()), true) }}</pre> --}}

            @if ($role === 'admin')
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption">
                        <label>Admin</label>
                    </li>

                    {{-- Dashboard (global, tidak pakai canView) --}}
                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'dashboard-admin' ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard-admin') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-airplay"></i></span>
                            <span class="nxl-mtext">Dashboard</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'data-member' ? 'active' : '' }}">
                        <a href="{{ route('admin.data-member') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">Data Member</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'instruktur' ? 'active' : '' }}">
                        <a href="{{ route('admin.instruktur') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-user-check"></i></span>
                            <span class="nxl-mtext">Instruktur</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'paket' ? 'active' : '' }}">
                        <a href="{{ route('admin.paket') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-package"></i></span>
                            <span class="nxl-mtext">Paket</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'clas' ? 'active' : '' }}">
                        <a href="{{ route('admin.clas') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-layers"></i></span>
                            <span class="nxl-mtext">Class</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'transaksi' ? 'active' : '' }}">
                        <a href="{{ route('admin.transaksi') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-file-text"></i></span>
                            <span class="nxl-mtext">Invoice</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'transaksi-clas' ? 'active' : '' }}">
                        <a href="{{ route('admin.transaksi-clas') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                            <span class="nxl-mtext">Transaksi Class</span>
                        </a>
                    </li>

                    {{-- <li class="nxl-item nxl-hasmenu {{ $path[1] === 'produk' ? 'active' : '' }}">
                        <a href="{{ route('admin.produk') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-layout"></i></span>
                            <span class="nxl-mtext">Produk</span>
                        </a>
                    </li> --}}

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'oprasional' ? 'active' : '' }}">
                        <a href="{{ route('admin.oprasional') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-truck"></i></span>
                            <span class="nxl-mtext">Operasional</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'absen' ? 'active' : '' }}">
                        <a href="{{ route('admin.absen') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-clock"></i></span>
                            <span class="nxl-mtext">Absensi</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'laporan' ? 'active' : '' }}">
                        <a href="{{ route('admin.laporan') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-book"></i></span>
                            <span class="nxl-mtext">Laporan</span>
                        </a>
                    </li>

                    <li class="nxl-item nxl-hasmenu {{ $path[1] === 'banner' ? 'active' : '' }}">
                        <a href="{{ route('admin.banner') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-tv"></i></span>
                            <span class="nxl-mtext">Banner</span>
                        </a>
                    </li>

                    {{-- Cetak Label --}}
                    {{-- <li class="nxl-item nxl-hasmenu {{ $path[1] === 'tools' ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-tool"></i></span>
                            <span class="nxl-mtext">Tools</span><span class="nxl-arrow"><i
                                    class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li
                                class="nxl-item {{ isset($path[2]) && $path[2] === 'cetak-label-rak' ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('superadmin.cetak-label-rak') }}">Cetak Label
                                    Rak</a>
                            </li>
                        </ul>
                    </li> --}}
                </ul>
            @endif
            {{-- <div class="card text-center">
                <div class="card-body">
                    <i class="feather-sunrise fs-4 text-dark"></i>
                    <h6 class="mt-4 text-dark fw-bolder">Downloading Center</h6>
                    <p class="fs-11 my-3 text-dark">Duralux is a production ready CRM to get started up and running
                        easily.</p>
                    <a href="javascript:void(0);" class="btn btn-primary text-dark w-100">Download Now</a>
                </div>
            </div> --}}
        </div>
    </div>
</nav>
