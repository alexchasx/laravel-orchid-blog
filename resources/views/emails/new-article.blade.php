<div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; color: #222; max-width: 600px; margin: 0 auto;">
    <h2 style="font-size: 20px; margin: 0 0 16px;">Новая статья в блоге {{ config('app.name') }}</h2>

    <p>Здравствуйте! Вышла новая статья:</p>

    <h3 style="margin: 16px 0 8px;">
        <a href="{{ $url }}" style="color: #0057ff; text-decoration: none;">{{ $article->title }}</a>
    </h3>

    @if($article->excert)
        <p style="color: #555;">{{ Str::limit($article->excert, 300) }}</p>
    @endif

    <p style="margin: 20px 0;">
        <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; background: #0057ff; color: #fff; text-decoration: none; border-radius: 6px;">Читать статью →</a>
    </p>

    @if($unsubscribeUrl)
        <p style="margin-top: 32px; color: #888; font-size: 12px;">
            Если вы больше не хотите получать такие письма —
            <a href="{{ $unsubscribeUrl }}" style="color: #888;">отпишитесь от рассылки</a>.
        </p>
    @endif
</div>
