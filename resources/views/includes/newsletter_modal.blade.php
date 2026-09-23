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
            <form class="subscribe-form" data-newsletter-form action="{{ route('subscribe.store') }}" method="POST">
                @csrf
                <label for="email">Ваш email</label>
                <div class="input-row">
                    <input id="email" name="email" type="email" autocomplete="email" placeholder="developer@example.com" required>
                    <button class="button primary" type="submit">Подписаться</button>
                </div>
                <p class="form-message" aria-live="polite"></p>
            </form>
        </div>

        {{-- Состояние «результат»: показывается после отправки формы --}}
        <div class="newsletter-result-view" {{ $subscribeFlash ? '' : 'hidden' }}>
            <span class="modal-icon {{ $subscribeOk ? 'modal-icon--ok' : 'modal-icon--error' }}" aria-hidden="true">{!! $subscribeOk ? '&#10003;' : '!' !!}</span>
            <h3 class="modal-title" id="newsletter-result-title">{{ $subscribeOk ? 'Подписка оформлена' : 'Не получилось' }}</h3>
            <p class="modal-text">{{ $subscribeFlash }}</p>

            <button class="button primary modal-action" type="button" data-modal-close>Понятно</button>
        </div>
    </div>
</div>
