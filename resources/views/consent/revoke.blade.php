@extends('layouts.techlog')

@section('content')
<div class="consent-page-container">
    <div class="card reveal">
        <div class="section-head">
            <div>
                <h1>Отзыв согласия на обработку ПДн</h1>
            </div>
        </div>

        <p>
            В соответствии с ч. 4 ст. 9 и ст. 10.1 Федерального закона № 152-ФЗ
            «О персональных данных», вы можете отозвать своё согласие на обработку
            или распространение персональных данных.
        </p>

        <h3>Порядок отзыва</h3>
        <ol>
            <li>Выберите, что отзываете: комментарий, обращение через форму обратной связи или подписку на рассылку.</li>
            <li>Укажите тип отзыва: на <strong>обработку</strong> или <strong>распространение</strong> ПДн.</li>
            <li>Укажите номер комментария (для комментариев) или номер обращения (для обращений) и e-mail.</li>
        </ol>

        <p>
            <strong>Внимание:</strong><br>
            — Отзыв согласия на <strong>обработку</strong> ПДн для комментария → удаление комментария целиком
            (в течение {{ config('consent.revocation_days', 7) }} рабочих дней).<br>
            — Отзыв согласия на <strong>распространение</strong> ПДн → обезличивание комментария
            (имя заменяется на «Аноним», текст сохраняется); распространение прекращается
            в течение {{ config('consent.distribution.stop_days', 3) }} рабочих дней (ч. 4 ст. 9 № 152-ФЗ),
            обезличивание — в течение {{ config('consent.revocation_days', 7) }} рабочих дней.<br>
            — Отзыв согласия на <strong>обработку</strong> ПДн для обращения → удаление обращения
            (в течение {{ config('consent.revocation_days', 7) }} рабочих дней).<br>
            — Отзыв согласия на <strong>обработку</strong> ПДн для подписки → отписка от рассылки.
        </p>

        <form action="{{ route('consent.revoke') }}" method="post" class="comment-form consent-form" id="revoke-form">
            @csrf

            <label for="revoke-subject">
                Что отозвать
                <select id="revoke-subject" name="subject_type" required>
                    <option value="">— Выберите —</option>
                    <option value="comment" {{ old('subject_type', request('subject_type')) === 'comment' ? 'selected' : '' }}>
                        Комментарий
                    </option>
                    <option value="contact" {{ old('subject_type', request('subject_type')) === 'contact' ? 'selected' : '' }}>
                        Обращение через форму обратной связи
                    </option>
                    <option value="subscriber" {{ old('subject_type', request('subject_type')) === 'subscriber' ? 'selected' : '' }}>
                        Подписка на рассылку
                    </option>
                </select>
            </label>

            <div id="revoke-comment-fields" {{ in_array(old('subject_type', request('subject_type')), ['contact', 'subscriber']) ? 'hidden' : '' }}>
                <div class="input-row">
                    <label for="revoke-comment-id">
                        Номер комментария
                        <input id="revoke-comment-id" name="comment_id" type="number" min="1"
                            placeholder="Например: 42" value="{{ old('comment_id', request('comment_id')) }}"
                            {{ in_array(old('subject_type', request('subject_type')), ['contact', 'subscriber']) ? '' : 'required' }}>
                    </label>
                </div>
            </div>

            <div id="revoke-contact-fields" {{ old('subject_type', request('subject_type')) !== 'contact' ? 'hidden' : '' }}>
                <div class="input-row">
                    <label for="revoke-contact-id">
                        Номер обращения
                        <input id="revoke-contact-id" name="contact_id" type="number" min="1"
                            placeholder="Например: 15" value="{{ old('contact_id', request('contact_id')) }}"
                            {{ old('subject_type', request('subject_type')) !== 'contact' ? '' : 'required' }}>
                    </label>
                </div>
            </div>

            <label for="revoke-email">
                E-mail
                <input id="revoke-email" name="email" type="email"
                    placeholder="you@example.com" value="{{ old('email', request('email')) }}" required>
            </label>

            <label for="revoke-type">
                Тип согласия
                <select id="revoke-type" name="consent_type" required>
                    <option value="">— Выберите —</option>
                    <option value="processing" {{ old('consent_type', request('consent_type')) === 'processing' ? 'selected' : '' }}>
                        На обработку ПДн
                    </option>
                    <option value="distribution" {{ old('consent_type', request('consent_type')) === 'distribution' ? 'selected' : '' }}>
                        На распространение ПДн
                    </option>
                </select>
            </label>

            @error('subject_type')
                <span class="comment-error">{{ $message }}</span>
            @enderror
            @error('comment_id')
                <span class="comment-error">{{ $message }}</span>
            @enderror
            @error('contact_id')
                <span class="comment-error">{{ $message }}</span>
            @enderror
            @error('email')
                <span class="comment-error">{{ $message }}</span>
            @enderror
            @error('consent_type')
                <span class="comment-error">{{ $message }}</span>
            @enderror

            <button class="button primary" type="submit">Отозвать согласие →</button>
        </form>

        <p class="revoke-back-link">
            <a href="{{ url()->previous() ?: route('home') }}">← Вернуться на предыдущую страницу</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var subjectSelect = document.getElementById('revoke-subject');
    var commentFields = document.getElementById('revoke-comment-fields');
    var commentInput = document.getElementById('revoke-comment-id');
    var contactFields = document.getElementById('revoke-contact-fields');
    var contactInput = document.getElementById('revoke-contact-id');

    if (subjectSelect && commentFields && commentInput && contactFields && contactInput) {
        function toggleFields() {
            var val = subjectSelect.value;

            // comment_id
            if (val === 'comment') {
                commentFields.hidden = false;
                commentInput.required = true;
            } else {
                commentFields.hidden = true;
                commentInput.required = false;
            }

            // contact_id
            if (val === 'contact') {
                contactFields.hidden = false;
                contactInput.required = true;
            } else {
                contactFields.hidden = true;
                contactInput.required = false;
            }
        }

        subjectSelect.addEventListener('change', toggleFields);
        toggleFields();
    }
});
</script>
@endpush
