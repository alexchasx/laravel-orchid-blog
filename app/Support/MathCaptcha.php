<?php

namespace App\Support;

/**
 * Простая математическая капча: вопрос «a + b = ?»,
 * ответ хранится в сессии и сверяется при отправке формы.
 */
class MathCaptcha
{
    private const SESSION_KEY = 'captcha_answer';

    /**
     * Генерирует вопрос, кладёт ответ в сессию и возвращает текст для отображения.
     */
    public static function question(): string
    {
        $a = random_int(2, 12);
        $b = random_int(2, 12);

        session([self::SESSION_KEY => $a + $b]);

        return $a . ' + ' . $b . ' = ?';
    }

    /**
     * Проверяет ответ пользователя и однократно очищает ответ из сессии.
     */
    public static function check($value): bool
    {
        $answer = session(self::SESSION_KEY);

        session()->forget(self::SESSION_KEY);

        if ($answer === null) {
            return false;
        }

        $parsed = filter_var(trim((string) $value), FILTER_VALIDATE_INT);

        return $parsed !== false && $answer === $parsed;
    }
}