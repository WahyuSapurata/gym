@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $module }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    {{-- <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper" style="width: 250px;">
                        <select name="uuid_user" id="filter-outlet" class="form-select form-select-sm">
                            <option value="">Semua Outlet</option>
                            @foreach ($outlet as $o)
                                <option value="{{ $o->uuid_user }}">{{ $o->nama_outlet }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <!-- [Payment Records] start -->

                <!--! END: [Upcoming Schedule] !-->
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
@endpush
