@extends('admin.admin_layout')

@section('inner_content')

    <table class="admin-panel">
        <thead>
        <tr>
            <td>ID</td>
            <td>Заголовок</td>
            <td>Статус</td>
            <td></td>
            <td></td>
            <td></td>
            <td>Удалено</td>
            <td></td>
        </tr>
        </thead>

        <tbody>
        <tr>
            <form action="{{ route('tagStore') }}" method="post" role="form">
                <td></td>
                <td><input name="title" type="text" class="form-control" id="title" required></td>
                <td>
                    <label for="status">Показывать?</label>
                    <select name="status" id="status">
                        <option selected value="1">Да</option>
                        <option value="0">Нет</option>
                    </select>
                </td>
                <td></td>
                <td>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </td>
                <td>{{ csrf_field() }}</td>
                <td></td>
                <td></td>
            </form>
        </tr>

        @foreach($tags as $tag)

            <tr @if ($tag->deleted_at)
                    style="background-color: #e4b9b9;"
                @elseif (!$tag->status)
                    style="background-color: #9B859D;"
                @endif
            >
                <form action="{{ route('tagUpdate') }}" method="post" role="form">
                    <td>
                        <input name="id" type="hidden" class="form-control" value="{{$tag->id}}">
                        <input style="max-width: 50px" type="ta" class="form-control" id="id"
                               value="{{$tag->id}}"
                               disabled>
                    </td>
                    <td><input name="title" type="text" class="form-control" id="title"
                               value="{{ $tag->title }}" required></td>
                    <td>
                        <label for="status">Показывать?</label>
                        <select name="status" id="status">
                            <option
                                    @if ($tag->status)
                                    selected
                                    @endif
                                    value="1">Да
                            </option>
                            <option
                                    @unless ($tag->status)
                                    selected
                                    @endunless
                                    value="0">Нет
                            </option>
                        </select>
                    </td>
                    <td>{{ $tag->created_at }}</td>
                    <td>
                        <button type="submit" class="btn btn-warning">Сохранить изменения</button>
                        {{ csrf_field() }}
                    </td>
                </form>
                <td>
                    <form action="{{ route('tagDelete', ['id'=>$tag->id]) }}"
                          method="post">
                        <!-- <input type="hidden" name="_method" value="DELETE"> -->
                        {{method_field('DELETE')}}
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </td>
                <td>{{ $tag->deleted_at }}</td>
                <td><a class="btn btn-success"
                       href="{{ route('tagRestore',['tag'=>$tag->id]) }}"
                       role="button">Восстановить</a>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>

@endsection