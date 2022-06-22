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

@include('includes/comments_list')

<hr>

@include('includes/comments_form')

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
