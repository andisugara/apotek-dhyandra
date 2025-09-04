<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Login - Apotek Dhyandra</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
        <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    </head>

    <body id="kt_body" class="app-blank app-blank">
        <div class="d-flex flex-column flex-root" id="kt_app_root">
            <div class="d-flex flex-column flex-lg-row flex-column-fluid">
                <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
                    <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                        <div class="w-lg-500px p-10">
                            <form class="form w-100" action="{{ route('login.post') }}" method="POST">
                                @csrf
                                <div class="text-center mb-11">
                                    <h1 class="text-dark fw-bolder mb-3">Login</h1>
                                    <div class="text-gray-500 fw-semibold fs-6">Apotek Dhyandra</div>
                                </div>

                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="fv-row mb-8">
                                    <input type="text" placeholder="Email" name="email" autocomplete="off"
                                        class="form-control bg-transparent" />
                                </div>

                                <div class="fv-row mb-3">
                                    <input type="password" placeholder="Password" name="password" autocomplete="off"
                                        class="form-control bg-transparent" />
                                </div>

                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                    <div></div>
                                    <a href="#" class="link-primary">Lupa Password?</a>
                                </div>

                                <div class="d-grid mb-10">
                                    <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                        <span class="indicator-label">Login</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="w-lg-500px d-flex flex-stack px-10 mx-auto">
                        <div class="d-flex fw-semibold text-primary fs-base gap-5">
                            <a href="#" class="px-5">Kebijakan Privasi</a>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2"
                    style="background-image: url(assets/media/misc/auth-bg.png)">
                    <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                        <a href="../../demo1/dist/index.html" class="mb-0 mb-lg-12">
                            <img alt="Logo" src="{{ asset('assets/media/logos/custom-1.png') }}"
                                class="h-60px h-lg-75px" />
                        </a>
                        <img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-10 mb-lg-20"
                            src="{{ asset('assets/media/misc/auth-screens.png') }}" alt="" />
                        <h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-7">Apotek Dhyandra</h1>
                        <div class="d-none d-lg-block text-white fs-base text-center">
                            Sistem manajemen apotek yang memudahkan
                            <br />dalam pengelolaan persediaan dan transaksi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    </body>

</html>
