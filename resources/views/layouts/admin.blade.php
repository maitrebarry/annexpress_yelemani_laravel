@include('admin.partials.header')
<body>
    @include('admin.partials.navbar')

    <div class="wrapper">
        @include('admin.partials.sidebar')

        <main class="main-content">
            @hasSection('hero')
                @yield('hero')
            @else
                @php
                    $pageTitle = trim($__env->yieldContent('breadcrumb-active'));
                    $sectionTitle = trim($__env->yieldContent('breadcrumb-title'));
                @endphp
                <div class="mb-4 page-header">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('/admin/Homes/home') }}">Accueil</a></li>
                            @if($sectionTitle !== '')
                                <li class="breadcrumb-item">{!! $sectionTitle !!}</li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <h1 class="mb-0">{{ $pageTitle !== '' ? $pageTitle : strip_tags($sectionTitle) }}</h1>
                        <div class="d-flex gap-2">
                            @yield('breadcrumb-actions')
                            <a href="javascript:history.back()" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('modals')

    {{-- Toast de notification (session('notification')) : centralisé ici pour s'appliquer
         à toutes les pages admin, plutôt que d'exiger un @include par vue. --}}
    @include('admin.partials.set_flash')

    @include('admin.partials.foot')
    @yield('scripts')
</body>

</html>
