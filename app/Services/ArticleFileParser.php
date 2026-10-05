<?php

namespace App\Services;

use Illuminate\Support\Str;

class ArticleFileParser
{
    /**
     * Парсит markdown-файл статьи и извлекает поля.
     *
     * @param  \SplFileInfo  $file  Файл .md из директории _import_articles
     * @return array{prefix: string, title: string, excert: ?string, content_raw: string, meta_desc: ?string}
     *
     * @throws \InvalidArgumentException  Если файл битый или не найден.
     */
    public function parse(\SplFileInfo $file): array
    {
        $basename = $file->getBasename('.md');

        // 1. Извлекаем префикс (ведущие цифры).
        $prefix = '';
        if (preg_match('/^\d+/', $basename, $m)) {
            $prefix = $m[0];
        }

        // 2. Title: убираем префикс и следующий за ним разделитель.
        $title = preg_replace('/^\d+\s*[-–—._]?\s*/', '', $basename);

        if ($title === '' || $title === $basename) {
            throw new \InvalidArgumentException(
                "Файл «{$file->getFilename()}» не содержит title: не удалось отделить номер-префикс."
            );
        }

        // 3. Читаем содержимое, унифицируем переводы строк.
        $content = str_replace("\r\n", "\n", $file->getContents());
        $lines = explode("\n", $content);

        // 4. Ищем строку-заголовок «## meta_description».
        $metaIndex = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/^##\s+meta_description\s*$/i', trim($line))) {
                $metaIndex = $i;
                break;
            }
        }

        // 5. Разделяем content_raw и meta_desc.
        if ($metaIndex !== null) {
            $metaDesc = trim(implode("\n", array_slice($lines, $metaIndex + 1)));
            $contentRaw = trim(implode("\n", array_slice($lines, 0, $metaIndex)));
        } else {
            $metaDesc = null;
            $contentRaw = $content;
        }

        // 6. Excert: первая непустая строка из content_raw.
        $excert = $this->extractExcert($contentRaw);

        return [
            'prefix'      => $prefix,
            'title'       => $title,
            'excert'      => $excert,
            'content_raw' => $contentRaw,
            'meta_desc'   => $metaDesc,
        ];
    }

    /**
     * Извлекает excert из первой непустой строки content_raw.
     */
    private function extractExcert(string $contentRaw): ?string
    {
        $lines = explode("\n", $contentRaw);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            // Если строка — горизонтальный разделитель, пропускаем.
            if (preg_match('/^[-*_]{3,}\s*$/', $trimmed)) {
                continue;
            }

            // Снимаем markdown-разметку курсива *...* или _..._.
            if (preg_match('/^\*+(.*)\*+$/s', $trimmed, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/^_+(.*)_+$/s', $trimmed, $m)) {
                return trim($m[1]);
            }

            return $trimmed;
        }

        return null;
    }
}
