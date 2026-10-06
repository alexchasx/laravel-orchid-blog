<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\User;
use App\Services\ArticleFileParser;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:import
                            {--dir= : Директория с md-файлами статей (по умолчанию base_path("_import_articles"))}
                            {--rubric= : ID рубрики для всех статей}
                            {--user= : ID автора (по умолчанию первый админ)}
                            {--dry-run : Показать, что будет импортировано, без записи в БД и копирования файлов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импортирует статьи из markdown-файлов в БД';

    /**
     * Допустимые расширения изображений.
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = $this->option('dir') ?: base_path('_import_articles');
        $rubricId = $this->option('rubric') ?? 1;
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');

        // 1. Валидация рубрики.
        // if (empty($rubricId)) {
        //     $this->error('Опция --rubric обязательна. Пример: --rubric=1');

        //     return self::FAILURE;
        // }

        // if (!Rubric::withTrashed()->where('id', $rubricId)->exists()) {
        //     $this->error("Рубрика с ID {$rubricId} не найдена.");

        //     return self::FAILURE;
        // }

        // 2. Определение автора.
        $authorId = $this->resolveAuthorId($userId) ?? 1 ?? 2;
        if ($authorId === null) {
            $this->error('Невозможно определить автора: пользователи не найдены.');

            return self::FAILURE;
        }

        // 3. Сбор файлов.
        if (!is_dir($dir)) {
            $this->error("Директория не найдена: {$dir}");

            return self::FAILURE;
        }

        $files = [];
        foreach (glob("{$dir}/*.md") as $path) {
            $files[] = new \SplFileInfo($path);
        }

        natcasesort($files);
        $files = array_values($files);

        if (empty($files)) {
            $this->warn("В директории {$dir} не найдено .md-файлов.");

            return self::SUCCESS;
        }

        // 4. Парсинг и импорт.
        $parser = new ArticleFileParser();
        $results = [];
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Счётчик префиксов для warning о повторе.
        $prefixCount = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();

            try {
                $data = $parser->parse($file);
            } catch (\Throwable $e) {
                $results[] = [
                    'file'      => $filename,
                    'title'     => '',
                    'image'     => '',
                    'meta_desc' => '',
                    'status'    => "error: {$e->getMessage()}",
                ];
                $errors++;

                continue;
            }

            $title = $data['title'];
            $slug = Str::slug($title);
            $prefix = $data['prefix'];

            // Warning о повторе префикса.
            if ($prefix !== '') {
                if (isset($prefixCount[$prefix])) {
                    $this->warn("Warning: префикс «{$prefix}» встречается в файле «{$prefixCount[$prefix]}» и «{$filename}» — картинка будет общая.");
                }
                $prefixCount[$prefix] = $filename;
            }

            // 5. Проверка дубля.
            if (Article::withTrashed()->where('slug', $slug)->exists()) {
                $results[] = [
                    'file'      => $filename,
                    'title'     => $title,
                    'image'     => '',
                    'meta_desc' => $data['meta_desc'] ?? '',
                    'status'    => 'skipped',
                ];
                $skipped++;

                continue;
            }

            // 6. Поиск и копирование картинки.
            $imageDbPath = null;
            $imageReport = '—';

            if ($prefix !== '') {
                $imagePath = $this->findAndCopyImage($file, $dir, $prefix);

                if ($imagePath !== null) {
                    $imageDbPath = 'articles/' . basename($imagePath);
                    $imageReport = 'articles/' . basename($imagePath);
                } else {
                    $imageReport = '(нет картинки)';
                }
            }

            // 7. Создание статьи.
            if ($dryRun) {
                $results[] = [
                    'file'      => $filename,
                    'title'     => $title,
                    'image'     => $imageReport,
                    'meta_desc' => $data['meta_desc'] ?? '',
                    'status'    => 'planned',
                ];

                continue;
            }

            try {
                DB::transaction(function () use ($data, $authorId, $rubricId, &$imageDbPath, &$slug) {
                    $article = Article::create([
                        'user_id'     => $authorId,
                        'rubric_id'   => $rubricId,
                        'title'       => $data['title'],
                        'excerpt'     => $data['excerpt'],
                        'content_raw' => $data['content_raw'],
                        'slug'        => $slug,
                        'image'       => $imageDbPath,
                        'meta_desc'   => $data['meta_desc'],
                        'is_published' => false,
                        'published_at' => null,
                    ]);

                    // Сохраняем фактический slug (возможно, изменён booted() при коллизии).
                    $slug = $article->slug;
                });

                $results[] = [
                    'file'      => $filename,
                    'title'     => $title,
                    'image'     => $imageReport,
                    'meta_desc' => $data['meta_desc'] ?? '',
                    'status'    => 'imported',
                ];
                $imported++;
            } catch (\Throwable $e) {
                $results[] = [
                    'file'      => $filename,
                    'title'     => $title,
                    'image'     => $imageReport,
                    'meta_desc' => $data['meta_desc'] ?? '',
                    'status'    => "error: {$e->getMessage()}",
                ];
                $errors++;
            }
        }

        // 8. Табличный отчёт.
        if (!empty($results)) {
            $this->table(
                ['Файл', 'Title', 'Картинка', 'meta_desc', 'Статус'],
                $results
            );
        }

        // 9. Итоговая сводка.
        $this->newLine();
        $this->info("Импортировано: {$imported}, пропущено: {$skipped}, ошибок: {$errors}, всего файлов: " . count($files));

        return self::SUCCESS;
    }

    /**
     * Определяет ID автора.
     *
     * @return int|null
     */
    private function resolveAuthorId(?string $userId): ?int
    {
        if ($userId !== null) {
            if (User::where('id', $userId)->exists()) {
                return (int) $userId;
            }

            $this->error("Пользователь с ID {$userId} не найден.");

            return null;
        }

        // Первый админ.
        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first();

        if ($admin) {
            return $admin->id;
        }

        // Первый пользователь.
        $first = User::first();

        if ($first) {
            $this->warn('Администраторы не найдены, используется первый пользователь: ' . $first->name);

            return $first->id;
        }

        return null;
    }

    /**
     * Ищет и копирует изображение статьи в public storage.
     *
     * @return string|null  Абсолютный путь скопированного файла или null.
     */
    private function findAndCopyImage(\SplFileInfo $file, string $dir, string $prefix): ?string
    {
        $imagesDir = "{$dir}/images";

        if (!is_dir($imagesDir)) {
            return null;
        }

        foreach (self::IMAGE_EXTENSIONS as $ext) {
            $candidate = "{$imagesDir}/{$prefix}.{$ext}";

            if (file_exists($candidate)) {
                $originalName = basename($candidate);
                $targetPath = "articles/{$originalName}";

                Storage::disk('public')->put($targetPath, file_get_contents($candidate));

                return storage_path("app/public/{$targetPath}");
            }
        }

        return null;
    }
}
