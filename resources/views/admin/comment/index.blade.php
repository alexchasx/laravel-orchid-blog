@extends('admin.admin_layout')

@section('inner_content')

    <table class="admin-panel">
        <thead>
        <tr>
            <td style="min-width: 50px;">ID</td>
            <td style="min-width: 200px;">Текст</td>
            <td>target_id</td>
            <td style="min-width: 120px;">target_type</td>
            <td>user_id</td>
            <td>user_name</td>
            <td>Статус</td>
            <td>Создано</td>
            <td></td>
            <td></td>
            <td>Удалено</td>
            <td></td>
        </tr>
        </thead>

        <tbody>

        @foreach($comments as $comment)

            <tr>
                <form action="{{ route('commentUpdate') }}" method="post" role="form">
                    <td>
                        <input name="id" type="hidden" class="form-control"
                               value="{{$comment->id}}">
                        <input style="max-width: 50px" type="numeric" class="form-control" id="id"
                               value="{{$comment->id}}"
                               disabled>
                    </td>
                    <td><input name="content" class="form-control"
                               id="content" value="{{ $comment->content }}" required></td>
                    <td><input name="target_id" type="text" class="form-control" id="target_id"
                               value="{{ $comment->target_id }}" required></td>
                    <td><input name="target_type" type="text" class="form-control" id="target_type"
                               value="{{ $comment->target_type }}" required></td>
                    <td><input name="user_id" type="text" class="form-control" id="user_id"
                               value="{{ $comment->user->id }}" required></td>
                    <td>{{ $comment->user->name }}</td>
                    <td>
                        <label for="status">Показывать?</label>
                        <select name="status" id="status">
                            <option
                                    @if ($comment->status)
                                    selected
                                    @endif
                                    value="1">Да
                            </option>
                            <option
                                    @unless ($comment->status)
                                    selected
                                    @endunless
                                    value="0">Нет
                            </option>
                        </select>
                    </td>
                    <td>{{ $comment->created_at }}</td>
                    <td>
                        <button type="submit" class="btn btn-warning">Сохранить изменения</button>
                        {{ csrf_field() }}
                    </td>
                </form>
                <td>
                    <form action="{{ route('commentDelete', ['id'=>$comment->id]) }}"
                          method="post">
                        <!-- <input type="hidden" name="_method" value="DELETE"> -->
                        {{method_field('DELETE')}}
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </td>
                <td>{{ $comment->deleted_at }}</td>
                <td><a class="btn btn-success"
                       href="{{ route('commentRestore',['comment'=>$comment->id]) }}"
                       role="button">Восстановить</a>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>

@endsection