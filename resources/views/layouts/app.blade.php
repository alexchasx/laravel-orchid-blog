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


    <link rel="stylesheet" type="text/css" href="/css/app.css" />
    <!-- <link rel="stylesheet" type="text/css" href="/SkiFi/css/normalize.css" /> -->
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

    </div>
</body>

</html>
