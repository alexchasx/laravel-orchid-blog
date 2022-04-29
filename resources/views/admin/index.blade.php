@extends('admin.admin_layout')

@section('inner_content')

    <table class="admin-panel">
        <thead>
        <tr>
            <td>ID</td>
            <td>Заголовок</td>
            <td>Статус</td>
            <td>Аватар</td>
            <td>Создано</td>
            <td></td>
            <td></td>
            <td>Удалено</td>
            <td></td>
        </tr>
        </thead>
        <tbody>

        @foreach($articles as $article)

            <tr @if ($article->deleted_at)
                style="background-color: #e4b9b9;"
                @elseif (!$article->status)
                style="background-color: #9B859D;"
                    @endif>
                <td>{{ $article->id }}</td>
                <td>{{ $article->title }}</td>
                <td>
                    @if ($article->deleted_at)
                        Удалено
                    @elseif ($article->status)
                        Видно
                    @else
                        Скрыто
                    @endif
                </td>
                <td style="max-width:70px;">
                    @unless (empty($avatar = $article->files->last()))
                        <img class="media-object"
                             src="{{ asset('storage/app/'. $avatar) }}"
                             alt="image">
                    @endunless
                </td>
                <td>{{ $article->created_at }}</td>
                <td><a class="btn btn-warning"
                       href="{{ route('articleEdit',['id'=>$article->id]) }}"
                       role="button">Редактировать</a></td>
                <td>
                    <form action="{{ route('articleDelete', ['id'=>$article->id]) }}"
                          method="post">
                        <!-- <input type="hidden" name="_method" value="DELETE"> -->
                        {{method_field('DELETE')}}
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </td>
                <td>{{ $article->deleted_at }}</td>
                <td><a class="btn btn-success"
                       href="{{ route('articleRestore',['id'=>$article->id]) }}"
                       role="button">Восстановить</a></td>
            </tr>

        @endforeach
        </tbody>


    </table>

@endsection