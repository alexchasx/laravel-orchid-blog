@extends('admin.admin_layout')

@section('inner_content')

<table class="admin-panel">
    <thead>
    <tr>
        <td>ID</td>
        <td>Заголовок</td>
        <td>Статус</td>
        <td>Кол-во статей</td>
        <td></td>
        <td></td>
        <td>Удалено</td>
        <td></td>
    </tr>
    </thead>

    <tbody>
    <tr>
        <form action="{{ route('categoryStore') }}" method="post" role="form">
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

    @foreach($categories as $category)

        <tr>
            <form action="{{ route('categoryUpdate') }}" method="post" role="form">
                <td>
                    <input name="id" type="hidden" class="form-control" value="{{$category->id}}">
                    <input style="max-width: 50px" type="numeric" class="form-control" id="id"
                           value="{{$category->id}}"
                           disabled>
                </td>
                <td><input name="title" type="text" class="form-control" id="title"
                           value="{{ $category->title }}" required></td>
                <td>
                    <label for="status">Показывать?</label>
                    <select name="status" id="status">
                        <option
                                @if ($category->status)
                                selected
                                @endif
                                value="1">Да
                        </option>
                        <option
                                @unless ($category->status)
                                selected
                                @endunless
                                value="0">Нет
                        </option>
                    </select>
                </td>
                <td>{{ $category->articles->count() }}</td>
                <td>
                    <button type="submit" class="btn btn-warning">Сохранить изменения</button>
                    {{ csrf_field() }}
                </td>
            </form>
            <td>
                <form action="{{ route('categoryDelete', ['id'=>$category->id]) }}"
                      method="post">
                    <!-- <input type="hidden" name="_method" value="DELETE"> -->
                    {{method_field('DELETE')}}
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </td>
            <td>{{ $category->deleted_at }}</td>
            <td><a class="btn btn-success"
                   href="{{ route('categoryRestore',['category'=>$category->id]) }}"
                   role="button">Восстановить</a>
            </td>
        </tr>
    @endforeach
    </tbody>

</table>

@endsection