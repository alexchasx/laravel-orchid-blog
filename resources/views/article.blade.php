@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="article_card">
    <h3>{{ $article->title }}</h3>
    <div class="publication_date">{{ $article->published_at }}</div>

    {{-- @if (isAdmin())
    <button class="tag">
        <a href="{{ route('articleEdit',['id'=>$article->id]) }}">
    Редактировать
    </a>
    </button>
    @endif --}}

    <p>{!! $article->content !!}</p>
</div>
<br />

<div class="comments">

    {{-- @if(!empty($comments)) --}}
    @if($comments->first())
    <hr />
    <h3><a name="comments">Комментарии</a></h3>

    @foreach($comments as $comment)
    <div>
        <div class="response-text-left ">
            {{--@if (!empty($avatar = $comment->user->files->last()))--}}
            {{--<img style="max-width: 70px" class="media-object"--}}
            {{--src="{{ asset('storage/app/'. $avatar->path) }}"--}}
            {{--alt="image">--}}
            {{--@else--}}
            {{--<img style="max-width: 70px" class="media-object"--}}
            {{--src="{{ asset('storage/app/'. $empty) }}"--}}
            {{--alt="image">--}}
            {{--@endif--}}
            <div class="comment_user">{{$comment->user->name}}:</div class="comment_user">
            <div class="publication_date">{{$comment->created_at}}</div>
        </div>

        <div class="response-text-right">
            <p class="" data-wow-delay=".5s">
                {{$comment->content}}
            </p>

            @if ((Auth::check())
            && ((Auth::user()->id)
            === $comment->user_id || isAdmin()))
            <form action="{{ route('commentDelete', ['comment'=>$comment->id]) }}" method="post">
                {{method_field('DELETE')}}
                {{csrf_field()}}
                <button type="submit" class="btn btn-danger">Удалить
                </button>
            </form>
            @endif


            <!-- end foreach -->

            <!-- 			<div class="media response-info">
                                                <div class="media-left response-text-left wow fadeInRight animated animated" data-wow-delay=".5s">
                                                <a href="#">
                                                <img class="media-object" src="GoEasyOn/images/t2.jpg" alt="">
                                                </a>
                                                <h5><a href="#">Admin</a></h5>
                                                </div>
                                                <div class="media-body response-text-right wow fadeInRight animated animated" data-wow-delay=".5s">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit,There are many variations of passages of Lorem Ipsum available,
                                                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                                                <ul>
                                                <li>Mar 28,2016</li>
                                                <li><a href="single.html">Ответить</a></li>
                                                </ul>
                                                </div>
                                                <div class="clearfix"> </div>
                                                </div> -->

            <!-- end foreach -->

        </div>

        <div class="clearfix"></div>
    </div>

    @endforeach
    @endif
</div>

<hr>
<div class="article_card">
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
            <br>
            <textarea id="comment" name="comment" cols="35" rows="8" maxlength="65525" required=""></textarea>
        </p>
        <p class="comment-form-author">
            <label for="author">{{ __('Имя') }} <span class="required" aria-hidden="true">*</span></label>
            <br>
            <input id="author" name="author" type="text" value="" size="30" maxlength="245" required="">
        </p>
        <p class="comment-form-email">
            <label for="email">Email <span class="required" aria-hidden="true">*</span></label>
            <br>
            <input id="email" name="email" type="email" value="" size="30" maxlength="100" aria-describedby="email-notes" required="">
        </p>
        <p class="comment-form-url">
            <label for="url">{{ __('Веб-сайт') }}</label>
            <br>
            <input id="url" name="url" type="url" value="" size="30" maxlength="200">
        </p>
        <p class="comment-form-cookies-consent">
            <input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes">
            <label for="wp-comment-cookies-consent">
                Сохраните мои данные в этом браузере для следующего комментария.
            </label>
            <br>
        </p>
        <p class="form-submit">
            <input name="submit" type="submit" id="submit" class="submit" value="{{ __('Отправить') }}">
            <input type="hidden" name="comment_post_ID" value="53" id="comment_post_ID">
            <input type="hidden" name="comment_parent" id="comment_parent" value="0">
        </p>
        <p style="display: none;">
            <input type="hidden" id="akismet_comment_nonce" name="akismet_comment_nonce" value="29327e458e">
        </p>
        <p style="display: none !important;"><label>Δ
                <textarea name="ak_hp_textarea" cols="35" rows="8" maxlength="100"></textarea></label>
            <input type="hidden" id="ak_js_1" name="ak_js" value="1651769329423">
            <script>
                document.getElementById("ak_js_1").setAttribute("value", (new Date()).getTime());

            </script>
        </p>
    </form>
</div>

@endunless

@endsection
