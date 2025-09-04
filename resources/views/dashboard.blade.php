@extends('layout.app')
@section('title', 'Dashboard')

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col-->
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body p-9">
                    <!--begin::Heading-->
                    <div class="fs-2hx fw-bold mb-5">
                        Selamat Datang, {{ auth()->user()->name }}
                    </div>
                    <!--end::Heading-->

                    @if (session('error'))
                        <div class="alert alert-danger mb-5">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="fs-4 mb-7">
                        <p>
                            Anda login sebagai
                            <span class="fw-bold text-primary">{{ auth()->user()->role->name }}</span>
                        </p>
                    </div>

                    <div class="d-flex flex-wrap">
                        @if (auth()->user()->isSuperAdmin())
                            <!--begin::Quick Links (Super Admin)-->
                            <div class="d-flex flex-column me-7 mb-5">
                                <h3 class="mb-4">Menu Utama</h3>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('supplier.index') }}" class="d-flex align-items-center mb-3">
                                        <span class="bullet bullet-dot me-3"></span>
                                        <span class="fw-semibold fs-5">Manajemen Supplier</span>
                                    </a>
                                    <a href="{{ route('user.index') }}" class="d-flex align-items-center mb-3">
                                        <span class="bullet bullet-dot me-3"></span>
                                        <span class="fw-semibold fs-5">Manajemen User</span>
                                    </a>
                                </div>
                            </div>
                            <!--end::Quick Links-->
                        @endif

                        @if (auth()->user()->isApoteker())
                            <!--begin::Quick Links (Apoteker)-->
                            <div class="d-flex flex-column me-7 mb-5">
                                <h3 class="mb-4">Menu Utama</h3>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('supplier.index') }}" class="d-flex align-items-center mb-3">
                                        <span class="bullet bullet-dot me-3"></span>
                                        <span class="fw-semibold fs-5">Data Supplier</span>
                                    </a>
                                </div>
                            </div>
                            <!--end::Quick Links-->
                        @endif
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection
