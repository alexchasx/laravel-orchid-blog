{{-- Модальное окно: подписка на новые статьи --}}
@php
    $subscribeOk = (bool) session('newsletter-success');
    $subscribeFlash = $subscribeOk ? session('newsletter-success') : session('newsletter-error');
@endphp

<div class="modal {{ $subscribeFlash ? 'is-open' : '' }}" id="newsletter-modal" role="dialog" aria-modal="true" aria-labelledby="newsletter-modal-title">
    <div class="modal-overlay" data-modal-close></div>

    <div class="modal-card newsletter-modal">
        <button class="modal-close" type="button" data-modal-close aria-label="Закрыть окно">&times;</button>

        {{-- Состояние «форма»: открывается по кнопке «Подписаться» --}}
        <div class="newsletter-form-view" {{ $subscribeFlash ? 'hidden' : '' }}>
            <p>Подписываясь, вы соглашаетесь получать письма от {{ config('app.name') }}</p>

            <form class="subscribe-form" data-newsletter-form action="{{ route('subscribe.store') }}" method="POST">
                @csrf
                <label for="email">Ваш email</label>
                <div class="input-row">
                    <input id="email" name="email" type="email" autocomplete="email" placeholder="developer@example.com" required>
                    <button class="button primary" type="submit" id="newsletter-submit" disabled>Подписаться</button>
                </div>

                <label class="newsletter-consent">
                    <br />
                    <input type="checkbox" name="consent_processing" value="1"
                        id="newsletter-consent" class="consent-checkbox" required>
                    <span>Я даю согласие на обработку персональных данных
                        в соответствии с <a href="{{ route('consent.processing') }}" target="_blank" class="consent-link">Согласием на обработку персональных данных</a>
                        и <a href="{{ route('privacy') }}" target="_blank" class="consent-link">Политикой конфиденциальности</a>.
                    </span>
                </label>

                <p class="form-message" aria-live="polite"></p>
            </form>
        </div>

        {{-- Состояние «результат»: показывается после отправки формы --}}
        <div class="newsletter-result-view" {{ $subscribeFlash ? '' : 'hidden' }}>
            <span class="modal-icon {{ $subscribeOk ? 'modal-icon--ok' : 'modal-icon--error' }}" aria-hidden="true">{!! $subscribeOk ? '&#10003;' : '!' !!}</span>
            <h3 class="modal-title" id="newsletter-result-title">{{ $subscribeOk ? 'Подписка оформлена' : 'Не получилось' }}</h3>
            <!-- <p class="modal-text">{{ $subscribeFlash }}</p> -->

            <button class="button primary modal-action" type="button" data-modal-close>Понятно</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var consent = document.getElementById('newsletter-consent');
    var submitBtn = document.getElementById('newsletter-submit');
    var form = document.querySelector('[data-newsletter-form]');

    if (consent && submitBtn) {
        consent.addEventListener('change', function () {
            submitBtn.disabled = !consent.checked;
        });
    }

    // Дополнительная проверка при отправке (на случай отключения JS).
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!consent || !consent.checked) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
