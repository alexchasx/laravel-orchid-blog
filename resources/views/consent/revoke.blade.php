@extends('layouts.techlog')

@section('content')
<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <div class="card reveal">
        <div class="section-head">
            <div>
                <h2>Отзыв согласия на обработку ПДн</h2>
            </div>
        </div>

        <p>
            В соответствии с ч. 4 ст. 9 и ст. 10.1 Федерального закона № 152-ФЗ
            «О персональных данных», вы можете отозвать своё согласие на обработку
            или распространение персональных данных.
        </p>

        <h3>Порядок отзыва</h3>
        <ol>
            <li>Укажите номер комментария, на который ссылается согласие.</li>
            <li>Укажите тип отзыва: на <strong>обработку</strong> или <strong>распространение</strong> ПДн.</li>
            <li>Укажите e-mail, указанный при оставлении комментария.</li>
        </ol>

        <p>
            <strong>Внимание:</strong><br>
            — Отзыв согласия на <strong>обработку</strong> ПДн приводит к удалению комментария целиком
            (в течение {{ config('consent.revocation_days', 7) }} рабочих дней).<br>
            — Отзыв согласия на <strong>распространение</strong> ПДн приводит к обезличиванию комментария
            (имя заменяется на «Аноним», текст сохраняется); распространение прекращается
            в течение {{ config('consent.distribution.stop_days', 3) }} рабочих дней (ч. 4 ст. 9 № 152-ФЗ),
            обезличивание — в течение {{ config('consent.revocation_days', 7) }} рабочих дней.
        </p>

        <form action="{{ route('consent.revoke') }}" method="post" class="comment-form" style="margin-top: 20px;">
            @csrf

            <div class="input-row">
                <label for="revoke-comment-id">
                    Номер комментария
                    <input id="revoke-comment-id" name="comment_id" type="number" min="1"
                        placeholder="Например: 42" value="{{ old('comment_id', request('comment_id')) }}" required>
                </label>

                <label for="revoke-email">
                    E-mail владельца комментария
                    <input id="revoke-email" name="email" type="email"
                        placeholder="you@example.com" value="{{ old('email', request('email')) }}" required>
                </label>
            </div>

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

            @error('comment_id')
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

        <p style="margin-top: 20px;">
            <a href="{{ url()->previous() ?: route('home') }}">← Вернуться на предыдущую страницу</a>
        </p>
    </div>
</div>
@endsection
