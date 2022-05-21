@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="article_card">
    <h3> @if (! Auth::guest() && isAdmin())
        [ID={{ $article->id }}]
        @endif

        {{ $article->title }}</h3>

    <div class="publication_date">{{ $article->published_at }}</div>

    {{-- @if (! Auth::guest() && isAdmin()) --}}
    {{-- <button class="tag">
        <a href="{{ route('articleEdit',['id'=>$article->id]) }}">
    {{ __('Редактировать') }}
    </a>
    </button> --}}
    {{-- @endif --}}

    <p>{!! $article->content_html !!}</p>
</div>
<br />

<div class="comments">

    {{-- @if(!empty($comments)) --}}
    @if($comments)
    <hr />
    <h3><a name="comments">{{ __('Комментарии') }}</a></h3>

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
            <div class="comment_user">{{$comment->name}}:</div class="comment_user">
                <br>
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
                <button type="submit" class="btn btn-danger">{{ __('Удалить') }}</button>
            </form>
            @endif
            <br>
            <hr>


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
    <h3>{{ __('Оставить ответ') }}</h3>
    <form action="{{ route('commentStore') }}" method="post" id="commentform" class="comment-form" novalidate="">
        @csrf
        <p class="comment-notes">
            <span id="email-notes">{{ __('Ваш адрес электронной почты не будет опубликован.') }}</span>
            <span class="required-field-message" aria-hidden="true">
                {{ __('Обязательные поля отмечены') }}<span class="required star" aria-hidden="true">*</span>
            </span>
        </p>
        <p class="comment-form-comment textwrapper">
            <label for="comment">
                {{ __('Комментарий') }} <span class="required star" aria-hidden="true">*</span>
                @error('comment')
                    <br>
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </label>
            <br>
            <textarea id="comment" name="comment" cols="35" rows="8" maxlength="65525" required="required">{{ old('comment') }}</textarea>
        </p>
        <p class="comment-form-author">
            <label for="author">{{ __('Ваше имя') }} <span class="required star" aria-hidden="true">*</span></label>
                @error('author')
                    <br>
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            <br>
            <input id="author" name="author" type="text" value="{{ old('author') }}" size="30" maxlength="245" required="">
        </p>
        <p class="comment-form-email">
            <label for="email">{{ __('Ваш email') }} <span class="required star" aria-hidden="true">*</span> (никто не увидит)</label>
                @error('email')
                    <br>
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            <br>
            <input id="email" name="email" type="email" value="{{ old('email') }}" size="30" maxlength="100" aria-describedby="email-notes" required="">
        </p>
        <p class="comment-form-url">
            <label for="website">{{ __('Веб-сайт (если есть)') }}</label>
                @error('website')
                    <br>
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            <br>
            <input id="website" name="website" type="url" value="{{ old('website') }}" size="30" maxlength="200">
        </p>
        {{-- <p class="comment-form-cookies-consent">
            <label for="checkbot">
                {{ __('1 + 2 =') }}
            </label>
                @error('checkbot')
                    <br>
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            <br>
            <input id="checkbot" name="checkbot" type="url" value="" size="30" maxlength="200">
        </p> --}}
        {{-- <p class="comment-form-cookies-consent">
            <input id="check_save_data" name="check_save_data" type="checkbox" value="yes">
            <label for="check_save_data">
                {{ __('Сохраните мои данные в этом браузере для следующего комментария.') }}
            </label>
            <br>
        </p> --}}
        <p class="form-submit">
            <input name="submit" type="submit" id="submit" class="submit" value="{{ __('Отправить') }}">
            <input type="hidden" name="article_id" value="{{ $article->id }}" id="article_id">
            {{-- <input type="hidden" name="comment_parent" id="comment_parent" value="0"> --}}
        </p>
        {{-- <p style="display: none;">
            <input type="hidden" id="akismet_comment_nonce" name="akismet_comment_nonce" value="29327e458e">
        </p> --}}
        {{-- <p style="display: none !important;"><label>Δ
                <textarea name="ak_hp_textarea" cols="35" rows="8" maxlength="100"></textarea></label>
            <input type="hidden" id="ak_js_1" name="ak_js" value="1651769329423"> --}}
            {{-- <script>
                document.getElementById("ak_js_1").setAttribute("value", (new Date()).getTime());
            </script> --}}
        </p>
    </form>
</div>

@endunless

@push('scripts')
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script> --}}
<script type="text/javascript">
// $(document).ready(function () {
//     $('#commentform').on('submit', function (e) {
//         e.preventDefault();

//         $.ajax({
//             type: 'POST',
//             url: '/blog/ajax/validation/store',
//             data: $('#commentform').serialize(),
//             success: function (data) {
//                 if (data.result) {
//                     $('#senderror').hide();
//                     $('#sendmessage').show();
//                 } else {
//                     $('#senderror').show();
//                     $('#sendmessage').hide();
//                 }
//             },
//             error: function () {
//                 $('#senderror').show();
//                 $('#sendmessage').hide();
//             }
//         });
//     });
// });

    // $(document).ready(function() {
    //     $(".submit").click(function(e) {
            // e.preventDefault();

            // const data = new FormData(this);
            // $.ajax({
            //     type: 'POST',
            //     url: ' ',
            //     data: data,
            //     dataType: 'json',
            //     processData: false,
            //     contentType: false,

            //     success: function(data) {
            //         console.log(data);
            //     },

            //     error: function(data) {
            //         console.log(data);
            //     }
            // })

            // let _token = $("input[name='_token']").val();
            // let comment = $("#comment").val();
            // let author = $("#author").val();
            // let email = $("#email").val();
            // let website = $("#website").val();
            // let check_bot = $("#check_bot").val();

            // $.ajax({
            //     type: 'POST',
            //     url: " ",
            //     data: {
            //         _token:_token,
            //         comment:comment,
            //         author:author,
            //         email:email,
            //         website:website
            //     },
            //     success: function(data) {
            //       console.log(data);
            //         // if($.isEmptyObject(data.error)){
            //         //     alert(data.success);
            //         // }
            //         // else{
            //         //     printErrorMsg(data.error);
            //         // }
            //         if (data.error.comment || data.error.check_bot || data.error.author) {
            //             alert('Обязательное поле (с символом *) не заполнено.');
            //         } else {

            //         }
            //     }
            // });
        // });

        // function printErrorMsg (msg) {
        //     $.each( msg, function( key, value ) {
        //     console.log(key);
        //       $('.'+key+'_err').text(value);
        //     });
        // }
    // });
</script>
@endpush

@endsection
