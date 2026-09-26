<div class="comment-form-wrap reveal">
    <div class="section-head">
        <div>
            <h2>Оставить комментарий</h2>
        </div>
    </div>

    <form action="{{ route('commentStore') }}" method="post" class="comment-form" id="commentform">
        @csrf

        @guest
            <div class="input-row" style="display: block">
                <label for="c-name">
                    {{ __('Имя') }}

                    @error('name')
                        <span class="comment-error">{{ $message }}</span>
                    @enderror

                    <input id="c-name" name="name" type="text" autocomplete="name" placeholder="Алексей" value="{{ old('name') }}" required minlength="2">
                </label>

                <label for="c-email">
                    {{ __('Email') }}

                    @error('email')
                        <span class="comment-error">{{ $message }}</span>
                    @enderror

                    <input id="c-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}" required>
                </label>
            </div>

            <label for="captcha">
                {{ __('Контрольный вопрос:') }} <strong>{{ $captcha }}</strong>

                @error('captcha')
                    <span class="comment-error">{{ $message }}</span>
                @enderror

                <input id="captcha" name="captcha" type="text" inputmode="numeric" autocomplete="off" placeholder="Ваш ответ" required>
            </label>
        @endguest

        <label for="comment">
            {{ __('Комментарий') }}

            @error('comment')
                <span class="comment-error">{{ $message }}</span>
            @enderror
        </label>

        <textarea id="comment" name="comment" rows="6" maxlength="65525" required placeholder="Поделитесь мыслями…">{{ old('comment') }}</textarea>

        <input type="hidden" name="article_id" value="{{ $article->id }}">

        <!-- Согласия на обработку ПДн (152-ФЗ) -->
        <div class="consent-checks">
            <label>
                <input type="checkbox" name="consent_processing" value="1"
                    {{ old('consent_processing') ? 'checked' : '' }}
                    id="consent-processing" style="max-width: 16px; cursor: pointer;" required>
                <span>Я даю согласие на обработку персональных данных
                    в соответствии с <a href="{{ route('consent.processing') }}" target="_blank" style="color: #00ff88;">Политикой конфиденциальности</a>.
                </span>
                @error('consent_processing')
                    <span class="comment-error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                <input type="checkbox" name="consent_distribution" value="1"
                    {{ old('consent_distribution') ? 'checked' : '' }}
                    id="consent-distribution" style="max-width: 16px; cursor: pointer;" required>
                <span>Я даю согласие на распространение (публичное
                    отображение) моих данных — имени/никнейма и текста
                    комментария на сайте.
                    <a href="{{ route('consent.distribution') }}" target="_blank" rel="noopener" style="color: #00ff88;">
                        Текст согласия
                    </a>.
                </span>
                @error('consent_distribution')
                    <span class="comment-error">{{ $message }}</span>
                @enderror
            </label>

            <label for="distribution-conditions">
                Дополнительные условия и запреты
                (необязательно, до {{ config('consent.distribution.max_conditions_length') }} символов)

                <textarea id="distribution-conditions" name="distribution_conditions"
                    rows="3" maxlength="{{ config('consent.distribution.max_conditions_length') }}"
                    placeholder="Например: Запрещаю использовать текст комментария для обучения ИИ...">{{ old('distribution_conditions') }}</textarea>
            </label>
        </div>

        <button class="button primary" type="submit" id="comment-submit" disabled>Отправить →</button>

        @push('scripts')
        <script>
        // Активация кнопки отправки формы комментария.
        // Кнопка активна только когда отмечены оба чекбокса согласий.
        document.addEventListener('DOMContentLoaded', function () {
            var processing = document.getElementById('consent-processing');
            var distribution = document.getElementById('consent-distribution');
            var submitBtn = document.getElementById('comment-submit');

            function updateButton() {
                submitBtn.disabled = !(processing.checked && distribution.checked);
            }

            if (processing && distribution && submitBtn) {
                processing.addEventListener('change', updateButton);
                distribution.addEventListener('change', updateButton);
                // Пересчитать состояние при загрузке (для old() после ошибок валидации).
                updateButton();
            }
        });
        </script>
        @endpush
    </form>
</div>
