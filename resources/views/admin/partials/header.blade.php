<!doctype html>
<html lang="en" class="minimal-theme">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TransHub Admin')</title>
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" sizes="32x32" />
  <link rel="icon" href="{{ asset('assets/images/favicon-16x16.png') }}" type="image/png" sizes="16x16" />
  <!--plugins-->
  <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <!-- Bootstrap CSS -->
  <link href="{{ asset('assets/css/bootstrap1.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

  <!-- loader-->
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />

  <!--Theme Styles-->
  <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/light-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style1.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/select2/css/bootstrap-custom-colors.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/table_styles.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/js/bs-stepper.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/transgest-theme.css') }}?v={{ @filemtime(public_path('assets/css/transgest-theme.css')) }}" rel="stylesheet" />
  <link href="{{ asset('assets/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4/animate.min.css">
</head>
