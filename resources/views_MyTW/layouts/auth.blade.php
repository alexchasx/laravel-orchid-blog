<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page.title', config('app.name'))</title>
    <link rel="icon" href="favicon.ico"><!-- 32×32 -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}" /> --}}
    <link rel="stylesheet" href="/css/style.css" />
</head>

<body>
    <div class="main_block" id="app">

        @include('includes.alert')

        @include('includes.header')

        <section>
            <div class="wrap">

                <main class="left_block">
                    @yield('content')
                </main>

                <aside class="right_block">
                    @include('includes.right_block')
                </aside>

            </div>
        </section>

        @include('includes.footer')

    </div>
</body>
@stack('js')
<script src="/js/app.js"></script>
{{-- <s
cript s
rc="{{ mix('/js/app.js') }}"></s> --}}

</html>