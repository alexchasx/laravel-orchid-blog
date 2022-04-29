@extends('admin.admin_layout')

@section('inner_content')

    <div class="container">
        <div class="row">
            <form action="{{ route('articleUpdate') }}" method="post" role="form">
                <p>Поля, обозначенные звёздочкой (&#10033;), обязательны для заполнения.</p>

                <div class="form-group">
                    <label for="id">ID</label>
                    <input name="id" type="hidden" class="form-control" value="{{$article->id}}">
                    <input type="numeric" class="form-control" id="id" value="{{$article->id}}"
                           disabled>
                </div>
                <div class="form-group">
                    <label for="title">Заголовок</label>&nbsp;&#10033;
                    <input name="title" type="text" class="form-control" id="title"
                           value="{{$article->title}}" required>
                </div>
                <div class="form-group">
                    <label for="categories_id">Категория</label>&nbsp;&#10033;
                    <select name="categories_id" size="3" class="form-control" id="categories_id"
                            required>
                        @foreach ($categories as $category)
                            <option
                                    @if($article->categories_id == $category->id)
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
                    <textarea name="description" rows="4" class="form-control"
                              id="description" required>{{$article->description}}</textarea>
                </div>
                <div class="form-group">
                    <label for="content">Полный текст</label>&nbsp;&#10033;
                    <textarea name="content" rows="25" class="form-control" id="content"
                              required>{{$article->content}}</textarea>
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
                           value="{{$article->meta_desc}}">
                </div>
                <div class="form-group">
                    <label for="keywords">Мета: ключевые слова</label>
                    <input name="keywords" type="text" class="form-control" id="keywords"
                           value="{{$article->keywords}}">
                </div>
                <div class="form-group">
                    <label for="tags_id">Все теги:</label>
                    <select name="tags_id[]" size="5" class="form-control" id="tags_id"
                            multiple>
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
            <!-- 			<div class="form-group">
				<label for="created_at">Дата создания</label>
				<input name="created_at" type="text" class="form-control" id="created_at" value="{{time()}}">
			</div>
			<div class="form-group">
				<label for="updated_at">Дата создания</label>
				<input name="updated_at" type="text" class="form-control" id="updated_at" value="{{time()}}">
			</div> -->

                <button type="submit" class="btn btn-primary">Сохранить</button>

                {{ csrf_field() }}
            </form>

            <label for="tags">Выбранные теги:</label>
            <table>
                @if (!$article->tags->count())
                    Тегов нет.
                @endif

                @foreach ($article->tags as $tag)
                    <tr>
                        <td>{{$tag->title}}</td>
                        <td>
                            <form action="{{ route('articleTagDelete', ['article'=>$article->id, 'tag'=>$tag->id]) }}"
                                  method="post">
                                <!-- <input type="hidden" name="_method" value="DELETE"> -->
                                {{method_field('DELETE')}}
                                {{csrf_field()}}
                                <button type="submit" class="btn-twitter btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

@endsection