<div class="comments">

    {{-- @if(!empty($comments)) --}}
    @if($comments->isNotEmpty())
    <hr />
    <h3>{{ __('Комментарии') }}</h3>

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
            <div class="publication_date">
                <time>{{ $comment->created_at->diffForHumans() }}</time>
            </div>

        </div>

        <div class="response-text-right">
            <p class="" data-wow-delay=".5s">
                {{$comment->content}}
            </p>

            @auth
            @hasAccess('platform.index')
            <form action="{{ route('commentDelete', $comment) }}" method="post">
                {{method_field('DELETE')}}
                {{csrf_field()}}
                <button type="submit" class="btn btn-danger">{{ __('Удалить') }}</button>
            </form>
            @endhasAccess
            @endauth

            <br>
            <hr>
        </div>
        <div class="clearfix"></div>
    </div>

    @endforeach
    @endif
</div>
