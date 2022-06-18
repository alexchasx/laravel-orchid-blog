<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet"> -->

    <!-- Styles -->
    <!-- <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/my_tw.css"> -->


    <link rel="stylesheet" type="text/css" href="/SkiFi/css/normalize.css" />
    <link rel="stylesheet" type="text/css" href="/SkiFi/css/demo.css" />
    <link rel="stylesheet" type="text/css" href="/SkiFi/css/main.css" />
    <link rel="stylesheet" type="text/css" href="/SkiFi/css/my.css" />

</head>
<!-- <body class="antialiased"> -->

<body>
    <div class="content">

        @include('includes.alert')

        @include('includes.header')

        @yield('content')

        <nav class="codrops-demos" style="margin-bottom:120px;">
            <a class="current-demo" href="index.html">Набор 1</a>
            <a href="index2.html">Набор 2</a>
        </nav>
    </div>
    <script>
        // Только для демострации (данный код нужен для показа эффектов на мобильных устройствах)
        [].slice.call(document.querySelectorAll('a[href="#"')).forEach(function(el) {
            el.addEventListener('click', function(ev) {
                ev.preventDefault();
            });
        });
    </script>
</body>

</html>
