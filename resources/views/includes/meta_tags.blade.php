@if(isset($article))
<title>{{ $article->title }} | {{ env('APP_NAME') }}</title>
<meta name='description' content='{{ $article->meta_desc }}' />
<meta property='article:published_time' content='{{$article->published_at}}' />
<meta property='article:modified_time' content='{{$article->updated_at}}' />

<meta property="og:type" content="article" />
<meta property="og:title" content="{{ $article->title }}" />
<meta property="og:description" content="{{ $article->meta_desc }}" />
<meta property="og:site_name" content="{{ env('APP_NAME') }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:locale" content="ru-RU" />
<meta property="og:locale:alternate" content="en-SU" />

<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="{{ env('APP_NAME') }}" />
<meta name="twitter:title" content="{{ $article->title }}" />
<meta name="twitter:description" content="{{ $article->meta_desc }}" />
@else
<title>{{ $meta_title }}</title>
<meta name='description' content='{{ $meta_desc }}' />
@endif
