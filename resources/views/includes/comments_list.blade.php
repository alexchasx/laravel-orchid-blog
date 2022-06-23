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
            === $comment->user_id))
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
