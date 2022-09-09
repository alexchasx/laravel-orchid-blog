<div id="app" class="article_card">
    <a name="comments_form"></a>

    @guest
    <h3>
        <a href="{{ route('login') }}" class="link login">
            <span>{{ 'Авторизуйтесь' }}</span>
        </a>
        , чтобы прокомментировать.
    </h3>

    @else

    <form action="{{ route('commentStore') }}" method="post" id="commentform" class="comment-form">
        @csrf

        <p class="comment-form-comment textwrapper">
            <label for="comment">
                {{ __('Комментарий') }}

                @error('comment')
                <br>
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror

            </label>
            <br>
            <textarea id="comment" name="comment" cols="35" rows="8" maxlength="65525" required="required">
                {{ old('comment') }}
            </textarea>
        </p>

        <p class="comment-form-cookies-consent">
        </p>

        <p class="form-submit">
            <input name="submit" type="submit" id="submit" class="submit" value="{{ __('Отправить') }}">
            <input type="hidden" name="article_id" value="{{ $article->id }}" id="article_id">
        </p>
    </form>

    @endguest
</div>
