@extends('layouts.base')

@section('content')
@unless (empty($article))
<div class="article_card">
    <h3>{{ $article->title }}</h3>
    <div class="publication_date">{{ $article->published_at }}</div>
    <p>{!! $article->content !!}</p>
</div>
<br />
<hr />

<h3>Оставить ответ</h3>
<form action="https://hbgl.dev/wp-comments-post.php" method="post" id="commentform" class="comment-form" novalidate="">
    <p class="comment-notes">
        <span id="email-notes">{{ __('Ваш адрес электронной почты не будет опубликован.') }}</span>
        <span class="required-field-message" aria-hidden="true">
            {{ __('Обязательные поля отмечены.') }}
            <span class="required" aria-hidden="true">*</span>
        </span>
    </p>
    <p class="comment-form-comment">
        <label for="comment">
            {{ __('Комментарий') }} <span class="required" aria-hidden="true">*</span>
        </label>
        <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required=""></textarea>
    </p>
    <p class="comment-form-author">
        <label for="author">{{ __('Имя') }} <span class="required" aria-hidden="true">*</span></label>
        <input id="author" name="author" type="text" value="" size="30" maxlength="245" required="">
    </p>
    <p class="comment-form-email">
        <label for="email">Email <span class="required" aria-hidden="true">*</span></label>
        <input id="email" name="email" type="email" value="" size="30" maxlength="100" aria-describedby="email-notes" required="">
    </p>
    <p class="comment-form-url">
        <label for="url">{{ __('Веб-сайт') }}
        </label>
        <input id="url" name="url" type="url" value="" size="30" maxlength="200">
    </p>
    <p class="comment-form-cookies-consent">
        <input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes">
        <label for="wp-comment-cookies-consent">Сохраните мои данные в этом браузере для следующего комментария.</label>

    </p>
    <p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="{{ __('Отправить') }}"> <input type="hidden" name="comment_post_ID" value="53" id="comment_post_ID">
        <input type="hidden" name="comment_parent" id="comment_parent" value="0">
    </p>
    <p style="display: none;">
        <input type="hidden" id="akismet_comment_nonce" name="akismet_comment_nonce" value="29327e458e">
    </p>
    <p style="display: none !important;"><label>Δ
            <textarea name="ak_hp_textarea" cols="45" rows="8" maxlength="100"></textarea></label>
        <input type="hidden" id="ak_js_1" name="ak_js" value="1651769329423">
        <script>
            document.getElementById("ak_js_1").setAttribute("value", (new Date()).getTime());

        </script>
    </p>
</form>


@endunless

@endsection
