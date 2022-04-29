@extends('admin.admin_layout')

@section('inner_content')

    <div class="container">
        <div class="row">

            @if (!empty($message))
                <div class="alert alert-danger text-center">
                    {{$message}}
                </div>
            @endif

            <form action="{{ route('articleStore') }}" method="post" role="form"
                  enctype="multipart/form-data">
                <h2>Создать статью</h2>
                <br>
                <p>Поля, обозначенные звёздочкой (&#10033;), обязательны для заполнения.</p>
                <br>

                <div class="form-group">
                    <label for="title">Заголовок</label>&nbsp;&#10033;
                    <input name="title" type="text" class="form-control" id="title" required
                           value="{{--{{$faker->text(50)}}--}}">
                </div>
                <div class="form-group">
                    <label for="categories_id">Категория</label>&nbsp;&#10033;
                    <select name="categories_id" size="3" class="form-control" id="categories_id"
                            required>
                        @foreach ($categories as $category)
                            <option
                                    @if (1 === $category->id)
                                    selected
                                    @endif
                                    value="{{$category->id}}">{{$category->title}}</option>
                        @endforeach
                    </select>
                </div>
                {{--<div class="form-group">--}}
                    {{--<label for="file">Загрузить файл</label><!--&nbsp;&#10033;--}}
                    {{--<input type="hidden" name="MAX_FILE_SIZE" value="500000"/> -->--}}
                    {{--<input name="file" type="file" class="form-control" id="file">--}}
                {{--</div>--}}
                <div class="form-group">
                    <label for="description">Краткое описание</label>&nbsp;&#10033;
                    <textarea name="description" class="form-control textarea" id="description"
                              required>{{--{{$faker->text(200)}}--}}</textarea>
                </div>
                <div class="form-group">
                    <label for="content">Полный текст</label>&nbsp;&#10033;
                    <textarea name="content" class="form-control textarea" id="content" required
                    >{{--{{$faker->text(2000)}}--}}</textarea>
                </div>
                <div class="form-group">
                    <input name="user_id" type="hidden" class="form-control" id="user_id"
                           value="{{Auth::user()->id}}">
                </div>
                <div class="form-group">
                    <label for="status">Показывать?</label>
                    <select name="status" id="status">
                        <option selected value="1">Да</option>
                        <option value="0">Нет</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="meta_desc">Мета: описание</label>
                    <input name="meta_desc" type="text" class="form-control" id="meta_desc"
                           value="{{--{{$faker->text(50)}}--}}" required>
                </div>
                <div class="form-group">
                    <label for="keywords">Мета: ключевые слова</label>
                    <input name="keywords" type="text" class="form-control" id="keywords"
                           value="{{--{{$faker->text(50)}}--}}" required>
                </div>
                <div class="form-group">
                    <label for="tags_id">Теги</label>&nbsp;&#10033;
                    <select name="tags_id[]" size="5" class="form-control" id="tags_id"
                            required multiple>
                        @foreach ($tags as $tag)
                            <option
                                    @if (1 === $tag->id)
                                    selected
                                    @endif
                                    value="{{$tag->id}}">{{$tag->title}}
                            </option>
                        @endforeach
                    </select>
                </div>
            <!-- 	<div class="form-group">
				<label for="created_at">Дата создания</label>
				<input name="created_at" type="text" class="form-control" id="created_at" value="{{date('Y-m-d')}}">
			</div> -->

                <button type="submit" class="btn btn-primary">Сохранить</button>

                {{ csrf_field() }}
            </form>
        </div>
    </div>

@endsection