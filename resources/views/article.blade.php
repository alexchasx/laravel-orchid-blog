@extends('layouts.app')

@section('content')

<div class="blog">
    <!-- container -->
    <div class="container">

        @unless(empty($article))

        <h3 class="text-center">{{ $article->title }}</h3>
        @if (isAdmin())
        <a class="btn btn-warning" href="{{ route('articleEdit',['id'=>$article->id]) }}" role="button">Редактировать
        </a>
        @endif

        <div class="col-md-8 blog-top-left-grid">
            <div class="left-blog left-single">
                <div class="blog-left">
                    <div class="single-left-left wow fadeInRight animated animated" data-wow-delay=".5s">
                        <p>Статья от <a href="#">{{ $article->user->name }}</a>
                            &nbsp;&nbsp; {{$article->created_at}} &nbsp;&nbsp;
                            <a href="#comments">
                                @unless (empty($commentsAll = $article->comment))
                                (Комментариев: {{ $commentsAll->count() }} )
                                @endunless
                            </a>
                        </p>
                        @if (isset($image->path))
                        <img class="media-object" src="{{ asset('storage/app/'. $image->path) }}" alt="image">
                        @endif
                    </div>

                    <!-- foreach -->

                    <div class="blog-left-bottom wow fadeInRight animated animated" data-wow-delay=".5s">
                        <p>
                            {!! $article->content !!}
                        </p>
                    </div>

                    <!-- end foreach -->

                </div>
                <div class="response">

                    @if(!empty($comments[0]))
                    <h3 class="wow fadeInRight animated animated" data-wow-delay=".5s">
                        {{--<a--}}
                        {{--name="comments">Комментарии</a></h3>--}}
                        @foreach($comments as $comment)

                        <div class="media response-info">
                            <div class="media-left response-text-left wow fadeInRight animated animated" data-wow-delay=".5s">
                                {{--@if (!empty($avatar = $comment->user->files->last()))--}}
                                {{--<img style="max-width: 70px" class="media-object"--}}
                                {{--src="{{ asset('storage/app/'. $avatar->path) }}"--}}
                                {{--alt="image">--}}
                                {{--@else--}}
                                {{--<img style="max-width: 70px" class="media-object"--}}
                                {{--src="{{ asset('storage/app/'. $empty) }}"--}}
                                {{--alt="image">--}}
                                {{--@endif--}}
                                <h5>{{$comment->user->name}}:</h5>
                            </div>

                            <div class="media-body response-text-right">
                                <p class="wow fadeInRight animated animated" data-wow-delay=".5s">
                                    {{$comment->content}}
                                </p>
                                <ul class="wow fadeInRight animated animated" data-wow-delay=".5s">
                                    <li>{{$comment->created_at}}</li>

                                    <!-- <li><a href="#com">Ответить</a></li> -->

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
                                </ul>


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

                <div class="opinion">
                    <h3 class="wow fadeInRight animated animated" data-wow-delay=".5s">

                        {{--//TODO здесь работа с сессией--}}

                        @if(Auth::check())

                        <form action="{{ route('commentStore') }}" class="wow fadeInRight animated animated" method="post" role="form">
                            <h4>Написать комментарий</h4>

                            <input type="hidden" name="target_id" value="{{ $article->id }}">
                            <textarea name="content" id="content" rows="4" class="form-control" placeholder="Введите свой комментарий" required></textarea>
                            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">

                            {{ csrf_field() }}

                            <button class="btn btn-default" type="submit">Отправить
                            </button>
                        </form>
                        @else
                        <p><a href="{{ route('login') }}">Авторизуйся, чтобы
                                прокомментировать.</a></p>
                        {{--<input class="form-control" name="user_email" type="email"--}}
                        {{--placeholder="E-mail (обязательно)" required>--}}
                        {{--<input class="form-control" name="user_name" type="text"--}}
                        {{--placeholder="Имя (обязательно)" required>--}}
                        @endif

                        @if (Auth::check() && isAdmin())
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('file.store') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="file" class="col-md-4 control-label">Загрузить
                                    файл</label>
                                <div class="col-md-6">
                                    <input name="id" type="hidden" class="form-control" value="{{$article->id}}">
                                    <input id="file" type="file" class="form-control" name="file" required>
                                    <button type="submit" class="btn btn-info">Загрузить
                                    </button>
                                </div>
                            </div>
                        </form>
                        <hr>
                        @if (isset($image->path))
                        <form action="{{ route('file.destroy', ['article'=>$article->id]) }}" method="post">
                            <!-- <input type="hidden" name="_method" value="DELETE"> -->
                            {{method_field('DELETE')}}
                            {{csrf_field()}}
                            <button type="submit" class="btn btn-danger">Удалить
                            </button>
                        </form>
                        @endif
                        @endif
                </div>

            </div>
        </div>

        @endunless

        <div class="col-md-4 blog-top-right-grid">
            <div class="categories">
                <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">
                    {{ __('Категории') }}</h3>

                <ul>

                    @if (!empty($categories))
                    {{-- @foreach($categories as $category)

                    @unless (empty($articleCount = $category->articles->count()))
                    <li class="wow fadeInLeft animated animated" data-wow-delay=".5s">
                        <a href="{{ route('showByCategory', ['id' => $category->id] ) }}">{{$category->title}}</a>
                    <span class="post-count pull-right">
                        ({{$articleCount}})
                    </span>
                    </li>
                    @endunless

                    @endforeach --}}
                    @endif

                </ul>
            </div>
            <div class="comments">
                <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">Популярные
                    статьи</h3>

                @unless (empty($popular))
                @foreach($popular as $pop)

                <div class="comments-text wow fadeInLeft animated animated" data-wow-delay=".5s">
                    {{-- <div class="col-md-3 comments-left">
                        <a href="{{ route('articleShow', ['id' => $pop->id]) }}">
                    @if (!empty($avatar = $pop->files->last()))
                    <img class="media-object" src="{{ asset('storage/app/'. $avatar->path) }}" alt="image">
                    @else
                    <img class="media-object" src="{{ asset('storage/app/'. $empty) }}" alt="image">
                    @endif
                    </a>
                </div> --}}
                {{-- <div class="col-md-9 comments-right"> --}}
                <div class="comments-right">
                    <a href="{{ route('articleShow', ['id' => $pop->id]) }}">
                        {{$pop->title}}
                    </a>
                    <p>
                        <?/*= $article->getDate() */?>
                    </p>
                </div>
                <div class="clearfix"></div>
            </div>

            @endforeach
            @endunless

        </div>
        <div class="comments">
            <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">Последние
                статьи</h3>
            @unless (empty($recent))
            @foreach($recent as $rec)

            <div class="comments-text wow fadeInLeft animated animated" data-wow-delay=".5s">
                {{-- <div class="col-md-3 comments-left">
                    <a href="{{ route('articleShow', ['id' => $rec->id]) }}">
                @if (!empty($avatar = $rec->files->last()))
                <img class="media-object" src="{{ asset('storage/app/'. $avatar->path) }}" alt="image">
                @else
                <img class="media-object" src="{{ asset('storage/app/'. $empty) }}" alt="image">
                @endif
                </a>
            </div> --}}
            {{-- <div class="col-md-9 comments-right"> --}}
            <div class="comments-right">
                <a href="{{ route('articleShow', ['id' => $rec->id]) }}">
                    {{ $rec->title }}
                </a>
                <p>
                    <?/*= $article->getDate() */?>
                </p>
            </div>
            <div class="clearfix"></div>
        </div>

        @endforeach
        @endunless

    </div>
    @unless (empty($tags[0]))
    <div class="comments">
        <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">Поиск по тегам:</h3>
        <div style="border: solid 1px #000;
                                        border-radius: 5px;
                                        padding: 5px; ">
            @foreach($tags as $tag)
            <button class="tags">
                <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
                    {{ $tag->title }}
                </a>
            </button>
            @endforeach
        </div>
    </div>
    @endunless
</div>

<div class="clearfix"></div>
</div>
</div>
<!-- //container -->
<!-- //blog -->
<!--//end-inner-content-->

@endsection
