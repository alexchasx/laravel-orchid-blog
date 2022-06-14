<h2 class="mt-2">{{ trans_choice('comments.count', $article->comments_count) }}</h2>

{{-- @include ('blog/comments/_form') --}}

<comment-list :post-id="{{ $article->id }}" loading-comments="@lang('comments.loading_comments')" data-confir m="@lang('f
orms.comments.delete')">
</comment-list>

<!-- <comment-list :post-id="{{ $article->id }}">
</comment-list> -->
