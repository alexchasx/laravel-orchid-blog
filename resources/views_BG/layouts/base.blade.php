<!DOCTYPE html>
<html lang="ru" data-react-helmet="lang">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#6574CD">
    <!-- <meta data-react-helmet="true" name="twitter:creator" content="Guillaume Briday">
    <meta data-react-helmet="true" name="twitter:card" content="summary_large_image"> -->
    <meta data-react-helmet="true" property="og:type" content="article">
    <!-- <meta name="generator" content="Gatsby 4.14.1"> -->

    <script type="module">
        const e = "undefined" != typeof HTMLImageElement && "loading" in HTMLImageElement.prototype;
        e && document.body.addEventListener("load", (function(e) {
            const t = e.target;
            if (void 0 === t.dataset.mainImage) return;
            if (void 0 === t.dataset.gatsbyImageSsr) return;
            let a = null,
                n = t;
            for (; null === a && n;) void 0 !== n.parentNode.dataset.gatsbyImageWrapper && (a = n.parentNode), n = n.parentNode;
            const o = a.querySelector("[data-placeholder-image]"),
                r = new Image;
            r.src = t.currentSrc, r.decode().catch((() => {})).then((() => {
                t.style.opacity = 1, o && (o.style.opacity = 0, o.style.transition = "opacity 500ms linear")
            }))
        }), !0);
    </script>
    <title>Статьи | ChasDev</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="/css/style.css" />

    <meta name="description" content="Список всех статей, которые я опубликовал." data-react-helmet="true">
    <meta property="og:title" content="Articles | ChasDev" data-react-helmet="true">
    <meta property="og:description" content="Список всех статей, которые я опубликовал." data-react-helmet="true">
    <!-- <meta property="og:image" content="https://mugshotbot.com/m?theme=two_up&amp;mode=light&amp;color=indigo&amp;pattern=lines_in_motion&amp;image=7a772170&amp;hide_watermark=true&amp;url=https://guillaumebriday.fr/articles" data-react-helmet="true"> -->
    <meta name="twitter:title" content="Articles | ChasDev" data-react-helmet="true">
    <meta name="twitter:description" content="Список всех статей, которые я опубликовал." data-react-helmet="true">
</head>

<body>
    <div class="main_block">
        <div tabindex="-1" id="gatsby-focus-wrapper" style="outline: none;" class="wrap">
            <div class="flex flex-col min-h-screen font-sans leading-normal">
                <div class="fixed w-full z-50 top-0 left-0">
                    <div class="bg-indigo-500 h-1" style="width: 0%;"></div>
                </div>

                @include('includes.alert')

                @include('includes.header')

                <div class="flex-1">
                    <div style="opacity: 1; transform: translateY(0px); transition: all 125ms ease-in-out 0s;">
                        <main>

                            @yield('content')

                        </main>
                    </div>
                </div>

                @include('includes.footer')

            </div>
        </div>
        <!-- <div id="gatsby-announcer" aria-live="assertive" aria-atomic="true" style="position: absolute; top: 0px; width: 1px; height: 1px; padding: 0px; overflow: hidden; clip: rect(0px, 0px, 0px, 0px); white-space: nowrap; border: 0px;">
            <ya-tr-span data-index="264-0" data-translated="true" data-source-lang="en" data-target-lang="ru" data-value="Navigated to Articles" data-translation="Переход к статьям" data-type="trSpan">Переход к статьям</ya-tr-span>
        </div> -->
    </div>
    <!-- <script id="gatsby-script-loader">
        /*<![CDATA[*/
        window.pagePath = "/why-i-stopped-using-docker-for-local-development";
        window.___webpackCompilationHash = "b409321cca5d3de2436f"; /*]]>*/
    </script>
    <script id="gatsby-chunk-mapping">
        /*<![CDATA[*/
        window.___chunkMapping = {
            "polyfill": ["/polyfill-740b9719dcb458ef8cdc.js"],
            "app": ["/app-2ab629d23379c5ac58d1.js"],
            "component---src-pages-404-jsx": ["/component---src-pages-404-jsx-b18184d4722391964fa6.js"],
            "component---src-pages-categories-jsx": ["/component---src-pages-categories-jsx-1f3647064d3cdce52548.js"],
            "component---src-pages-index-jsx": ["/component---src-pages-index-jsx-fc27398239d585e5b521.js"],
            "component---src-pages-mon-profil-jsx": ["/component---src-pages-mon-profil-jsx-341154c42778d7d7448f.js"],
            "component---src-pages-podcast-jsx": ["/component---src-pages-podcast-jsx-cd5e2c09cf3a6007e2b2.js"],
            "component---src-pages-talks-jsx": ["/component---src-pages-talks-jsx-f796c69acbbaf345dfb5.js"],
            "component---src-posts-index-jsx": ["/component---src-posts-index-jsx-c209bd72a722adb308c0.js"],
            "component---src-templates-page-jsx": ["/component---src-templates-page-jsx-4060f2f3bf1bac67cb79.js"],
            "component---src-templates-podcast-jsx": ["/component---src-templates-podcast-jsx-7377eb91d570b92fd97d.js"],
            "component---src-templates-post-jsx": ["/component---src-templates-post-jsx-9f49232322b2e4a21a4f.js"]
        }; /*]]>*/
    </script>
    <script src="/polyfill-740b9719dcb458ef8cdc.js" nomodule=""></script>
    <script src="/app-2ab629d23379c5ac58d1.js" async=""></script>
    <script src="/a9a7754c-54bfc86ac5e7a5b47f94.js" async=""></script>
    <script src="/cb1608f2-74d161b3735bcb5279b1.js" async=""></script>
    <script src="/framework-160947053ec484a07409.js" async=""></script>
    <script src="/webpack-runtime-67ef22856d6dbac6f386.js" async=""></script> -->
    <!-- <div id="tr-popup" class="tr-popup" translate="no" data-hidden="true" data-invalid="true" data-disabled="false" lang="ru" data-expanded="false" data-menu="false" data-translation="Libérer de la place sur votre SSD en tant que développeur" style="top: 216px; left: 729px;" data-position="top" data-menu-position="right" data-submitted="false">
        <div class="tr-popup__block"><span class="tr-popup__title_original">Оригинальный текст:</span> <span class="tr-popup__value">Libérer de la place sur votre SSD en tant que développeur</span></div>
        <div class="tr-popup__block tr-popup__block_a"><span class="tr-popup__link tr-popup__link_suggest" data-action="expand">Предложить перевод</span><a href="https://translate.yandex.ru" class="tr-popup__link tr-popup__link_service" target="_blank" data-action="navigate"><span class="tr-popup__logo tr-popup__logo_company"></span><span class="tr-popup__logo tr-popup__logo_service"></span></a></div>
        <div class="tr-popup__block tr-popup__block_b"><textarea class="tr-popup__input" spellcheck="false" autocapitalize="off" autocorrect="off" autocomplete="off" maxlength="1000"></textarea>
            <div class="tr-popup__block tr-popup__block_submit"><span role="button" class="tr-popup__button tr-popup__button_submit" data-action="send">Отправить</span></div>
            <div class="tr-popup__overlay tr-popup__overlay_submitted">Спасибо, перевод отправлен</div>
        </div><span role="button" class="tr-popup__button tr-popup__button_close" data-action="clickClose"></span><span role="button" class="tr-popup__button tr-popup__button_menu" data-action="clickMenu"><span class="tr-popup__menu" data-action="disablePopup">Отключить подсказку с оригинальным текстом</span></span><span class="tr-popup__arrow"></span>
    </div> -->
</body>

</html>
