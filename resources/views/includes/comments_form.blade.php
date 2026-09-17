<div class="comment-form-wrap reveal">
    <div class="section-head">
        <div>
            <p class="eyebrow">JOIN THE DISCUSSION</p>
            <h2>Оставить комментарий</h2>
        </div>
    </div>

    @guest
        <p class="comment-login-note">
            <a href="{{ route('login') }}" class="text-link">Авторизуйтесь</a>, чтобы прокомментировать.
        </p>
    @else
        <form action="{{ route('commentStore') }}" method="post" class="comment-form" id="commentform">
            @csrf

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
    @endguest
</div>
