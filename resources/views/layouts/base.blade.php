<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page.title', config('app.name'))</title>
    @stack('css')
</head>

<body>

    <div class="main_block">

        {{-- @include('includes.alert') --}}
        @include('includes.header')

        <section>
            @yield('content')
        </section>

        {{-- @include('includes.footer') --}}
    </div>

    @stack('js')
</body>
</html>
