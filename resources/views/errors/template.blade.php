<div class="error-page">
    <div class="error-content">
        <h3 class="error-title"><i class="fa fa-warning text-yellow"></i>{{ $metaDesc }}</h3>
    </div>

    <a href="{{ route('home') }}" class="link">← {{ __('На главную') }}</a>

    @if (Auth::user() && Auth::user()->isAdmin())
    <p>{{ class_basename($exception->getPrevious() ? : $exception) }}</p>
    <p>{{ $exception->getPrevious() ? $exception->getPrevious()->getMessage()
                        : $exception->getMessage() }}</p>
    @endif

    <!-- /.error-content -->
</div>
<!-- /.error-page -->
