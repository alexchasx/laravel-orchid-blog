<footer>
    <hr />
    <div class="footer">
        <a href="{{ route('home') }}" class="link">{{ config('app.name') }}</a>
        <div class="made_in">Сделано на Laravel</div>
        <div class="made_in">2022
            @unless(Date('Y') == 2022)
            {{ '- ' . Date('Y') }}
            @endunless
        </div>
    </div>
</footer>
