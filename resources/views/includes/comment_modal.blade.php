{{-- Модальное окно: результат отправки комментария --}}
@php
    $commentFlash = session('success');
    $captchaError = $errors->has('captcha');
@endphp

@if($commentFlash || $captchaError)
<div class="modal is-open" id="comment-modal" role="dialog" aria-modal="true" aria-labelledby="comment-modal-title">
    <div class="modal-overlay" data-modal-close></div>

    <div class="modal-card">
        <button class="modal-close" type="button" data-modal-close aria-label="Закрыть окно">&times;</button>

        @if($captchaError)
            <span class="modal-icon modal-icon--error" aria-hidden="true">!</span>
            <h3 class="modal-title" id="comment-modal-title">Неверно набрано проверочное число</h3>
            <p class="modal-text">{{ $errors->first('captcha') }} Попробуйте ещё раз.</p>
        @else
            <span class="modal-icon modal-icon--ok" aria-hidden="true">&#10003;</span>
            <h3 class="modal-title" id="comment-modal-title">Комментарий отправлен</h3>
            <p class="modal-text">Комментарий отправлен на модерацию и появится на сайте после проверки.</p>
        @endif

        <button class="button primary modal-action" type="button" data-modal-close>Понятно</button>
    </div>
</div>
@endif
