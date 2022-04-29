@extends('layouts.app')

@section('content')
    <!--/start-inner-content-->
    <!-- blog -->
    <div class="blog">
        <!-- container -->
        <div class="container">


            @unless(empty($category))

                <div class="blog-info wow fadeInDown animated animated" data-wow-delay=".5s">
                    <h3 class="tittle">Статьи по категории: {{$category->title}}</h3>
                </div>

            @endunless

            @unless(empty($tag))

                <div class="blog-info wow fadeInDown animated animated" data-wow-delay=".5s">
                    <h3 class="tittle">Статьи по тегу: {{$tag->title}}</h3>
                </div>

            @endunless


            <div class="blog-top-grids">
                <div class="col-md-4 blog-top-right-grid">
                    <div class="categories">
                        <h4 class="wow fadeInLeft animated animated" data-wow-delay=".5s"></h4>
                        <br>
                        <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">
                            Категории
                        </h3>
                        <ul>

                            @if (!empty($categories))
                                @foreach($categories as $category)

                                    @unless (empty($articleCount = $category->articles->count()))
                                        <li class="wow fadeInLeft animated animated"
                                            data-wow-delay=".5s">
                                            <a href="{{ route('showByCategory', ['id' => $category->id] ) }}">{{$category->title}}</a>
                                            <span class="post-count pull-right">
                                        ({{ $articleCount }})
                                    </span>
                                        </li>
                                    @endunless

                                @endforeach
                            @endif

                        </ul>
                    </div>
                </div>

                <div class="col-md-8 blog-top-left-grid">
                    <div class="left-blog">

                        @unless (empty($articles))
                            @foreach ($articles as $article)

                                <div class="blog-left">

                                    <div class="blog-left-left wow fadeInRight animated animated"
                                         data-wow-delay=".5s">
                                        <hr>
                                        <p>Статья от {{$article->user->name}}
                                            &nbsp;&nbsp; {{$article->created_at}}
                                            &nbsp;&nbsp;
                                            (Комментариев: {{$article->comments->count()}}
                                            )</p>
                                        <a href="{{route('articleShow', ['id' => $article->id])}}">
                                            @unless (empty($avatar = $article->files->last()))
                                                <img class="media-object"
                                                     src="{{ asset('storage/app/'. $avatar->path) }}"
                                                     alt="image">
                                            @endunless
                                        </a>
                                    </div>

                                    <div class="blog-left-right wow fadeInRight animated animated"
                                         data-wow-delay=".5s">
                                        <a href="{{route('articleShow', ['id' => $article->id])}}">{{$article->title}}</a>
                                        <p>
                                            {!! $article->description !!}
                                        </p>
                                    </div>

                                    <ul class="text-center pull-right">
                                        <li><i class="fa fa-yey">Количество просмотров&nbsp;&nbsp;
                                            </i>{{(int)$article->viewed}}</li>
                                    </ul>

                                    <div class="clearfix"></div>
                                </div>

                            @endforeach

                    </div>
                    <nav>
                        <ul class="pagination wow fadeInRight animated animated"
                            data-wow-delay=".5s">
                            {{ $articles->links() }}
                        </ul>
                    </nav>

                    @endunless

                    <hr>
                </div>


                <div class="col-md-4 blog-top-right-grid">

                    @unless (empty($popular[0]))
                        <div class="comments">
                            <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">
                                Популярные
                                статьи</h3>

                            @foreach($popular as $pop)

                                <div class="comments-text wow fadeInLeft animated animated"
                                     data-wow-delay=".5s">
                                    <div class="col-md-3 comments-left">
                                        <a href="{{ route('articleShow', ['id' => $pop->id]) }}">
                                            @if (!empty($avatar = $pop->files->last()))
                                                <img class="media-object"
                                                     src="{{ asset('storage/app/'. $avatar->path) }}"
                                                     alt="image">
                                            @else
                                                <img class="media-object"
                                                     src="{{ asset('storage/app/'. $empty) }}"
                                                     alt="image">
                                            @endif
                                        </a>
                                    </div>
                                    <div class="col-md-9 comments-right">
                                        <a href="{{ route('articleShow', ['id' => $pop->id]) }}">
                                            {{$article->title}}
                                        </a>
                                        <p>{{  $pop->created_at }}</p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                            @endforeach
                        </div>
                    @endunless

                    @unless (empty($recent[0]))
                        <div class="comments">
                            <h3 class="wow fadeInLeft animated animated" data-wow-delay=".5s">
                                Последние
                                статьи</h3>
                            @foreach($recent as $rec)

                                <div class="comments-text wow fadeInLeft animated animated"
                                     data-wow-delay=".5s">
                                    <div class="col-md-3 comments-left">
                                        <a href="{{ route('articleShow', ['id' => $rec->id]) }}">
                                            @if (!empty($avatar = $rec->files->last()))
                                                <img class="media-object"
                                                     src="{{ asset('storage/app/'. $avatar) }}"
                                                     alt="image">
                                            @else
                                                <img class="media-object"
                                                     src="{{ asset('storage/app/'. $empty) }}"
                                                     alt="image">
                                            @endif
                                        </a>
                                    </div>
                                    <div class="col-md-9 comments-right">
                                        <a href="{{ route('articleShow', ['id' => $rec->id]) }}">
                                            {{ $rec->title }}
                                        </a>
                                        <p>{{  $rec->created_at }}</p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                            @endforeach
                        </div>
                    @endunless

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
    </div>
    <!-- //blog -->
    <!--//end-inner-content-->

@endsection


