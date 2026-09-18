<div class="comment-form-wrap reveal">
    <div class="section-head">
        <div>
            <p class="eyebrow">JOIN THE DISCUSSION</p>
            <h2>Оставить комментарий</h2>
        </div>
    </div>

    @if(session('success'))
        <p class="form-message success">{{ session('success') }}</p>
    @endif

    <form action="{{ route('commentStore') }}" method="post" class="comment-form" id="commentform">
        @csrf

        @guest
            <div class="input-row">
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

        <button class="button primary" type="submit">Отправить →</button>
    </form>
</div>