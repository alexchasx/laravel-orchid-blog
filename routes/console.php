<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Автопубликация статей, чьё время публикации наступило.
Schedule::command('articles:publish-scheduled')->everyMinute();

// Обработка просроченных отзывов согласий (раз в сутки).
Schedule::command('consents:process-revocations')->daily();
