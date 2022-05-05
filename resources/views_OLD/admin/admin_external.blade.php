<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name') }}</title>
    <!-- bootstrap-css -->
    <link href="{{ asset('GoEasyOn/css/bootstrap.css') }}" rel="stylesheet" type="text/css"
          media="all"/>
    <!--// bootstrap-css -->
    <!-- font -->
    <link href='//fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:700,700italic,800,300,300italic,400italic,400,600,600italic'
          rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Playfair+Display:400,700,400italic'
          rel='stylesheet' type='text/css'>
    <!-- //font -->
    <!-- css -->
    <link rel="stylesheet" href="{{ asset('GoEasyOn/css/style.css') }}" type="text/css"
          media="all"/>
    <link rel="stylesheet" href="{{ asset('GoEasyOn/css/component.css') }}" type="text/css"
          media="all"/>
    <!--// css -->
    <!-- font-awesome icons -->
    <link href="{{ asset('GoEasyOn/css/font-awesome.css') }}" rel="stylesheet">
    <!-- //font-awesome icons -->
</head>
<body>
<div class="container demo-2" id="home">
    <!--carbonads-container-->
    <div class="content">
        <div class="w3_agile_menu">
            <div class="agileits_w3layouts_nav">
                <div id="toggle_m_nav">
                    <div id="m_nav_menu" class="m_nav">
                        <div class="m_nav_ham w3_agileits_ham" id="m_ham_1"></div>
                        <div class="m_nav_ham" id="m_ham_2"></div>
                        <div class="m_nav_ham" id="m_ham_3"></div>
                    </div>
                </div>
                <div id="m_nav_container" class="m_nav wthree_bg">
                    <nav class="menu menu--sebastian">
                        <ul id="m_nav_list" class="m_nav menu__list">
                            <li class="m_nav_item active" id="m_nav_item_1">
                                <a href="{{ url('/') }}" class="link link--kumya">
                                    <i class="fa fa-home" aria-hidden="true"></i>
                                    <span data-letters="Главная">Главная</span>
                                </a>
                            </li>
                            <li class="m_nav_item" id="moble_nav_item_2">
                                <a href="{{ route('contact') }}" class="link link--kumya scroll">
                                    <span data-letters="О проекте">Контакты</span>
                                </a>
                            </li>

                            @if (Auth::guest())

                                <li class="m_nav_item" id="moble_nav_item_5">
                                    <a href="{{ route('login') }}" class="link link--kumya scroll">
                                        <span data-letters="Вход">Вход&nbsp;</span>
                                    </a>
                                </li>
                                <li class="m_nav_item" id="moble_nav_item_6">
                                    <a href="{{ route('register') }}"
                                       class="link link--kumya scroll">
                                        <span data-letters="Регистрация">Регистрация</span>
                                    </a>
                                </li>


                            @else

                                @if (isAdmin())
                                    <li class="m_nav_item" id="moble_nav_item_6">
                                        <a href="{{ route('adminIndex') }}"
                                           class="link link--kumya scroll">
                                            <span data-letters="Админ">Админ-панель</span>
                                        </a>
                                    </li>
                                @endif

                                <li class="m_nav_item" id="moble_nav_item_6">
                                    <a href="#" class="link link--kumya scroll">
                                        <span data-letters="{{ Auth::user()->name }}">Привет, {{ Auth::user()->name }}</span>
                                    </a>
                                </li>
                                <li class="m_nav_item" id="moble_nav_item_6">
                                    <a href="{{ route('logout') }}"
                                       class="link link--kumya scroll">
                                        <span data-letters="Выход">Выход</span>
                                    </a>
                                </li>

                            @endif

                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

@yield('content')

    <div class="clearfix"></div>

    <!-- //blog -->
    <!--//end-inner-content-->

    <!--copy-right-->
    <div class="copy">
        <p class="wow fadeInUp animated animated" data-wow-delay=".5s">
            <a href="{{ route('contact') }}">© {{ config('app.name') }}</a>, 2017
            @if (date('Y', time()) > 2017)
                - {!! date('Y', time()) !!}
            @endif
            .
            Все права защищены.
        <div> Блог сделан на
            <a href="https://laravel.com/">
                <em>Laravel</em>
            </a>
        </div>
        </p>
    </div>
    <!--//copy-right-->
    <!--//footer-->
    <a href="#home" id="toTop" class="scroll" style="
    display: block;
    filter: alpha(Opacity=50);
    opacity: 0.5;
"> <span id="toTopHover"
                                                                             style="opacity: 1;"> </span></a>
    <!--/script-->
    <script src="{{ asset('GoEasyOn/js/jquery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('GoEasyOn/js/modernizr.custom.js') }}"></script>
    <script type="text/javascript" src="{{ asset('GoEasyOn/js/menu.js') }}"></script>
    <script type="text/javascript" src="{{ asset('GoEasyOn/js/jquery.magnific-popup.js') }}"></script>
    <link href="{{ asset('GoEasyOn/css/magnific-popup.css') }}" rel="stylesheet" type="text/css">
    <script>
        $(document).ready(function () {
            $('.popup-with-zoom-anim').magnificPopup({
                type: 'inline',
                fixedContentPos: false,
                fixedBgPos: true,
                overflowY: 'auto',
                closeBtnInside: true,
                preloader: false,
                midClick: true,
                removalDelay: 300,
                mainClass: 'my-mfp-zoom-in'
            });
        });
    </script>
    <!--animate-->
    <link href="{{ asset('GoEasyOn/css/animate.css') }}" rel="stylesheet" type="text/css"
          media="all">
    <script src="{{ asset('GoEasyOn/js/wow.min.js') }}"></script>
    <script>
        new WOW().init();
    </script>
    <!--//end-animate-->
    <!-- menu -->
    <script type="text/javascript" src="{{ asset('GoEasyOn/js/main.js') }}"></script>
    <!-- //menu -->
    <script src="{{ asset('GoEasyOn/js/rAF.js') }}"></script>
    <script src="{{ asset('GoEasyOn/js/demo-2.js') }}"></script>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-48014931-1', 'codyhouse.co');
        ga('send', 'pageview');

        jQuery(document).ready(function ($) {
            $('.close-carbon-adv').on('click', function (event) {
                event.preventDefault();
                $('#carbonads-container').hide();
            });
            var domain = 'http://codyhouse.co/demo/stretchy-navigation/';
            $('.cd-demo-settings').on('change', function () {
                var animation = $('#selectAnimation').find("option:selected").val(),
                    newFile = animation + '.html';
                window.location.href = domain + newFile;
            });
        });
    </script>
    <!-- gallery Modals -->
    <script type="text/javascript" src="{{ asset('GoEasyOn/js/move-top.js') }}"></script>
    <script type="text/javascript" src="{{ asset('GoEasyOn/js/easing.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(".scroll").click(function (event) {
                event.preventDefault();
                $('html,body').animate({scrollTop: $(this.hash).offset().top}, 900);
            });
        });
    </script>
    <!--start-smooth-scrolling-->
    <script type="text/javascript">
        $(document).ready(function () {
            /*
            var defaults = {
                  containerID: 'toTop', // fading element id
                containerHoverID: 'toTopHover', // fading element hover id
                scrollSpeed:1000,
                easingType: 'linear'
             };
            */

            $().UItoTop({easingType: 'easeOutQuart'});

        });
    </script>
    <!-- for bootstrap working -->
    <script src="{{ asset('GoEasyOn/js/bootstrap.js') }}"></script>
    <!-- //for bootstrap working -->
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
//        $('textarea').ckeditor();
         $('.textarea').ckeditor(); // if class is prefered.
    </script>

</body>
</html>