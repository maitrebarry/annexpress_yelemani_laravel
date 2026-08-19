@include('admin.partials.header')
<body>
    <!--start wrapper-->
    <div class="wrapper">
        <!--start top header-->
        @include('admin.partials.navbar')
        <!--end top header-->

        <!--start sidebar -->
        @include('admin.partials.sidebar')
        <!--end sidebar -->

        <!--start content-->
        <main class="page-content">
            @hasSection('hero')
                @yield('hero')
            @else
                <!--breadcrumb-->
                <div class="page-breadcrumb d-flex flex-wrap align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">@yield('breadcrumb-title', 'Configuration')</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb-active')</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="ms-auto">
                        <div class="d-flex gap-2">
                            @yield('breadcrumb-actions')
                            <a href="javascript:history.back()"
                                class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
                                <i class="bx bx-left-arrow-alt fs-5"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <!--end breadcrumb-->
            @endif

            @yield('content')

        </main>
        <!--end page main-->

        <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->

        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
    </div>
    <!--end wrapper-->

    @yield('modals')

    @include('admin.partials.foot')
    @yield('scripts')
</body>

</html>
