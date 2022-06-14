<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="favicon.ico"><!-- 32×32 -->

    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="/css/style.css" /> -->

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" /> -->
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/my.css" />

    <title>@yield('page.title', config('app.name'))</title>
</head>

<body>
    <div class="main_block container border">
        <div class="row">

            @include('includes.alert')

            @include('includes.header')

            <section class="">
                <br>

                <div class="container text-bg-primary mb-3">
                    <div class="row">

                        <!-- Side bar -->
                        <aside class="col-lg-4" id="sidebar">

                            @include('includes.right_block')

                        </aside>

                        <main class="col-lg-8">

                            <div class="row">

                                @yield('content')

                            </div>

                        </main>
                    </div>

                </div>
            </section>

            <footer>Footer</footer>

        </div>
    </div>

    <!-- Modal -->
    @include('auth.login')
    @include('auth.register')

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script> -->

    <!-- <script src="/js/app.js"></script> -->
    <script src="/js/bootstrap.bundle.min.js"></script>
</body>
@stack('js')
























































































































































</html>
