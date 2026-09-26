<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\ConsentLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ProcessRevocations extends Command
{
    /**
     * Имя и сигнатура команды.
     */
    protected $signature = 'consents:process-revocations';

    /**
     * Описание команды.
     */
    protected $description = 'Обработка отзывов согласий, срок которых наступил';

    /**
     * Выполнить команду.
     */
    public function handle(): int
    {
        $this->info('Проверка отзывов согласий, срок обработки которых наступил...');

        // Сроки (в рабочих днях) зависят от типа согласия:
        // - распространение: прекратить распространение в течение 3 рабочих дней,
        //   затем обезличить комментарий;
        // - обработка: удалить персональные данные в течение 7 рабочих дней.
        $stopDays    = Config::get('consent.distribution.stop_days', 3);
        $deleteDays  = Config::get('consent.revocation_days', 7);

        // Только отозванные и ещё не обработанные согласия.
        $revokedLogs = ConsentLog::whereNotNull('revoked_at')
            ->whereNull('processed_at')
            ->with('comment')
            ->get();

        if ($revokedLogs->isEmpty()) {
            $this->info('Отзывов, ожидающих обработки, не найдено.');

            return Command::SUCCESS;
        }

        $processed = 0;

        foreach ($revokedLogs as $log) {
            $dueDays = $log->consent_type === ConsentLog::TYPE_DISTRIBUTION
                ? $stopDays
                : $deleteDays;

            // Срок обработки отсчитывается от момента отзыва (revoked_at).
            $dueAt = $log->revoked_at?->copy()->addWeekdays($dueDays);

            if (!$dueAt || $dueAt->gt(now())) {
                // Срок ещё не наступил — откладываем до следующего запуска.
                continue;
            }

            $comment = $log->comment;

            if (!$comment) {
                $this->warn("Лог #{$log->id} не привязан к комментарию (вероятно, комментарий удалён ранее). Помечаем обработанным.");
                $log->update(['processed_at' => now()]);
                $processed++;

                continue;
            }

            // Отзыв на распространение → обезличиваем комментарий.
            if ($log->consent_type === ConsentLog::TYPE_DISTRIBUTION) {
                if (!$comment->is_anonymized && !$comment->trashed()) {
                    $comment->update([
                        'name' => 'Аноним',
                        'is_anonymized' => true,
                    ]);
                    $this->info("Комментарий #{$comment->id} обезличен.");
                }
            }

            // Отзыв на обработку → удаляем комментарий целиком.
            if ($log->consent_type === ConsentLog::TYPE_PROCESSING) {
                if (!$comment->trashed()) {
                    $comment->forceDelete();
                    $this->info("Комментарий #{$comment->id} удалён.");
                }
            }

            $log->update(['processed_at' => now()]);
            $processed++;
        }

        $this->info("Обработано {$processed} отзывов.");

        return Command::SUCCESS;
    }
}
