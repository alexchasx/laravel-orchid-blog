{{-- Модальное окно: результат подписки на новые статьи --}}
@php
    $subscribeOk = (bool) session('newsletter-success');
    $subscribeFlash = $subscribeOk ? session('newsletter-success') : session('newsletter-error');
@endphp

<div class="modal {{ $subscribeFlash ? 'is-open' : '' }}" id="newsletter-modal" role="dialog" aria-modal="true" aria-labelledby="newsletter-modal-title">
    <div class="modal-overlay" data-modal-close></div>

    <div class="modal-card">
        <button class="modal-close" type="button" data-modal-close aria-label="Закрыть окно">&times;</button>

        <span class="modal-icon {{ $subscribeOk ? 'modal-icon--ok' : 'modal-icon--error' }}" aria-hidden="true">{!! $subscribeOk ? '&#10003;' : '!' !!}</span>
        <h3 class="modal-title" id="newsletter-modal-title">{{ $subscribeOk ? 'Подписка оформлена' : 'Не получилось' }}</h3>
        <p class="modal-text">{{ $subscribeFlash }}</p>

        <button class="button primary modal-action" type="button" data-modal-close>Понятно</button>
    </div>
</div>