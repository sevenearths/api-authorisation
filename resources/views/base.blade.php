<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <base href="{{ url('/') }}/">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Font Awsome: CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- Bootstrap: CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <style type="text/css">
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak { display: none !important; }
        body { background-color: #eee; }
        h1 { text-align: center; }
        .navbar { background-color: white; }
        .nav.nav-tabs { border-bottom: none; }
        .right-align { text-align: right; }
        .messages ul { margin-bottom: 0 !important; }
    </style>

    @yield('css')

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>
<body>
    <div id="app">

        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        @if(Session::has('user_logged_in'))
                            <li><a href="{{ route('logout') }}">Logout</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    @yield('content')

                </div>
            </div>
        </div>

    </div>

    <!-- Angular: JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular-animate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular-sanitize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular-touch.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/2.4.0/ui-bootstrap-tpls.min.js"></script>
    <script src="{{ url('/') }}/js/gm.dragDrop.js"></script>
    <script src="{{ url('/') }}/js/truncate.js"></script>

    @yield('script_angular')

    <!-- Angular: FACTORIES -->
    <script src="{{ url('/') }}/app/js/factories/MessagesFactory.js"></script>
    <script src="{{ url('/') }}/app/js/factories/RequestInterceptorFactory.js"></script>
    <script src="{{ url('/') }}/app/js/factories/httpInterceptorFactory.js"></script>

    <!-- Angular: CONTROLLERS -->
    <script src="{{ url('/') }}/app/js/controllers/MessagesController.js"></script>

    <script src="{{ url('/') }}/app/js/controllers/KeysController.js"></script>
    <script src="{{ url('/') }}/app/js/controllers/GroupsController.js"></script>
    <script src="{{ url('/') }}/app/js/controllers/UsersController.js"></script>
    <script src="{{ url('/') }}/app/js/controllers/UrlsController.js"></script>

    <!-- JQuery: JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Bootstrap: JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

    @yield('script_jquery')

</body>
</html>
