{{-- Cookie-баннер: уведомление о политике конфиденциальности и использовании куки.
     Показывается при первом визите (до загрузки трекинговых скриптов).
     Управляется ресурсом resources/js/cookie-banner.js (функция initCookieBanner()).
     Доступность: role="dialog", aria-modal="false" (не блокирует контент полностью),
     aria-labelledby / aria-describedby, закрытие крестиком, навигация с клавиатуры. --}}
<aside class="cookie-banner" id="cookie-banner" data-cookie-banner role="dialog" aria-modal="false" aria-labelledby="cookie-banner-title" aria-describedby="cookie-banner-text" hidden>
    <div class="cookie-banner__card">
        <button class="cookie-banner__close" type="button" data-cookie-close aria-label="Закрыть уведомление">&times;</button>

        <p class="eyebrow">PRIVACY &amp; COOKIES</p>
        <h2 class="cookie-banner__title" id="cookie-banner-title">Мы ценим вашу конфиденциальность</h2>
        <p class="cookie-banner__text" id="cookie-banner-text">
            Мы используем cookie и анонимную метрику, чтобы сайт работал стабильнее и был удобнее.
            Подробнее о данных — в <a href="{{ route('privacy') }}">Политике конфиденциальности</a>.
        </p>

        <div class="cookie-banner__actions">
            <button class="button primary" type="button" data-cookie-accept>Принять</button>
            <button class="button cookie-banner__decline" type="button" data-cookie-decline>Отклонить</button>
            <button class="button ghost cookie-banner__customize" type="button" data-cookie-customize aria-expanded="false" aria-controls="cookie-banner-settings">Настроить</button>
        </div>

        <div class="cookie-banner__settings" id="cookie-banner-settings" hidden>
            <p class="cookie-banner__settings-text">Разрешите только то, что считаете нужным:</p>
            <div class="cookie-banner__option">
                <input id="cookie-cat-analytics" type="checkbox" data-cookie-cat="analytics">
                <label for="cookie-cat-analytics">Аналитика (анонимная статистика посещений)</label>
            </div>
            <button class="button" type="button" data-cookie-save>Сохранить выбор</button>
        </div>
    </div>
</aside>