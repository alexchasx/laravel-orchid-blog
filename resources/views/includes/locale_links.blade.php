@if (App::isLocale('ru'))
<li class="left"><a href="{{ route('setlocale', ['locale' => 'en']) }}" class="link">English</a></li>
@else
<li class="left"><a href="{{ route('setlocale', ['locale' => 'ru']) }}" class="link">Русский</a></li>
@endif
