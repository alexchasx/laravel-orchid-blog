@auth
<comment-form :post-id="{{ $article->id }}" placeholder="@lang('comments.placeholder.content')"
    button="@lang('comments.comment')">
</comment-form>
@else
<x-alert type="warning">
    @lang('comments.sign_in_to_comment')
</x-aler
t>
@endauth