<html>

    <head>
        <link rel="stylesheet" type="text/css" href="/css/materialize.css">
        <link rel="stylesheet" type="text/css" href="/css/style.css">
        <script type="text/javascript" src="/js/jquery-2.1.1.js"></script>
        <script type="text/javascript" src="/js/materialize.js"></script>
        <script type="text/javascript" src="/js/script.js"></script>
    </head>

    <body>
    <header>
        <div class="row">
                <nav class="top-nav grey darken-3">
                    <div class="container">
                        <div class="nav-wrapper">
                            <a class="brand-logo center">@yield('title')</a>
                            <ul class="left">
                                <li><a href="#" data-activates="slide-out" class="button-collapse show-on-large">Меню</a></li>
                            </ul>
                        </div>
                    </div>
                </nav>
        </div>
        @include('sidebar')
    </header>

    <main>

            <div class="row">


                <div class="col s8 offset-s1">
                        @yield('content')
                </div>
            </div>

    </main>
    <footer>

    </footer>
    </body>
</html>