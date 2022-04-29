<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MySite') }}</title>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/my.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css?123') }}" media="screen, projection" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/demo.css?123') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/icons.css?123') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/categories.css?123') }}" />

</head>
<body>
    <div id="app wrapper">
        <div class="row">
            <nav class="navbar navbar-default navbar-top h-header">
                <div class="container h-header">
                    <a class="navbar-brand logo" href="{{ url('/') }}">
                        WEBAdeptus
                    </a>
                    <a class="navbar-brand logo" href="{{ route('contact') }}">
                        О проекте
                    </a>
                    <ul class="nav navbar-nav">

                        @if ( (Auth::check()) && (Auth::user()->role) == 'admin')

                        <li><a href="{{ route('adminIndex') }}">Панель администратора</a></li>

                        @endif

                    </ul>

                </div>
            </nav>
        </div>

        @if(count($errors) > 0)

        <div class="alert alert-danger">
            <ul>

              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach

          </ul>
      </div>

      @endif

      @yield('content')

      <footer class="footer"><!-- FOOTER =============== -->
<!--     <div class="footer__logo logo">
        <img src="" alt="logo" class="logo__img logo__img_small">
    </div> -->

    <div class="copy">
        <p class="copy text-center">&copy; 2017 WEBAdeptus</p>
        <br>
    </div>      
</footer><!-- FOOTER END =============== -->

<!-- <a href="#" class="btn btn-default up-button" role="button" title="Кнопка вверх">&#8657;</a> -->
</div><!-- wrapper END -->


    <!--[if lt IE 9]>
    <script src="libs/html5shiv/es5-shim.min.js"></script>
    <script src="libs/html5shiv/html5shiv.min.js"></script>
    <script src="libs/html5shiv/html5shiv-printshiv.min.js"></script>
    <script src="libs/respond/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="{{ asset('js/my.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="{{ asset('js/ie10-viewport-bug-workaround.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $(document).ready(function(){
            $(".add-to-cart").click(function () {
                var id = $(this).attr("data-id");
                $.post("/cart/addAjax/"+id, {}, function (data) {
                    $("#cart-count").html(data);
                });
                return false;
            });
        });
    </script>
</body>
</html>
