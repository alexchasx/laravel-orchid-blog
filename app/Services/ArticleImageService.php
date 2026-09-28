<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Orchid\Attachment\Models\Attachment;
use Orchid\Support\Facades\Toast;

class ArticleImageService
{
    /**
     * Параметры вариантов: [ширина, высота, качество].
     * Все варианты кадрируются 16:9 (cover) — без искажений пропорций.
     */
    private const SIZES = [
        'large'     => [1600, 900, 82],
        'medium'    => [800, 450, 80],
        'thumbnail' => [400, 225, 75],
    ];

    /** Максимальный размер загружаемого файла (5 МБ). */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /** Разрешённые MIME-типы. */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Сохраняет изображение статьи: копирует оригинал, генерирует варианты WebP,
     * обновляет поле $article->image, удаляет вложение из таблицы attachments.
     */
    public function store(Article $article, Attachment $attachment): void
    {
        $file = Storage::disk($attachment->disk)->path($attachment->physicalPath());

        if ($file === '' || !file_exists($file)) {
            Toast::error('Файл вложения не найден на диске.');

            return;
        }

        // Проверка размера.
        if (filesize($file) > self::MAX_FILE_SIZE) {
            Toast::error('Размер изображения превышает 5 МБ.');

            return;
        }

        // Проверка MIME.
        $mime = mime_content_type($file);
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            Toast::error('Допустимы только изображения JPEG, PNG или WebP.');

            return;
        }

        // Раскрываем расширение из вложения.
        $extension = $attachment->extension;
        $articleDir = "articles/{$article->id}";

        // Если у статьи уже есть изображение — удаляем старую папку.
        if (!empty($article->image)) {
            $this->removeFor($article);
        }

        // Копируем оригинал.
        $originalPath = "{$articleDir}/original.{$extension}";
        Storage::disk('public')->put($originalPath, file_get_contents($file));

        // Генерируем варианты WebP (16:9 cover).
        $manager = new ImageManager(new Driver());

        foreach (self::SIZES as $sizeName => [$width, $height, $quality]) {
            $image = $manager->read(file_get_contents($file));
            $image->cover($width, $height);
            $webpPath = "{$articleDir}/{$sizeName}.webp";
            Storage::disk('public')->put($webpPath, (string) $image->toWebp($quality));
        }

        // Записываем путь к medium.webp в статью.
        $article->update(['image' => "{$articleDir}/medium.webp"]);

        // Удаляем вложение из таблицы и физический файл (чтобы не плодить дубликаты).
        $attachment->delete();
    }

    /**
     * Удаляет все файлы изображения статьи (идемпотентно).
     */
    public function removeFor(Article $article): void
    {
        if (empty($article->image)) {
            return;
        }

        $articleDir = Str::before($article->image, '/');
        $articleId  = $article->id;
        $directory  = "articles/{$articleId}";

        Storage::disk('public')->deleteDirectory($directory);
    }

    /**
     * Возвращает зарегистрированное вложение для текущего изображения статьи
     * (для предпросмотра в поле Upload при редактировании).
     */
    public function previewAttachment(Article $article): ?Attachment
    {
        if (empty($article->image)) {
            return null;
        }

        // Ищем существующее вложение по пути.
        $existing = Attachment::query()
            ->where('disk', 'public')
            ->where('path', 'articles/' . $article->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        // Создаём фейковое вложение для предпросмотра.
        $filename = Str::afterLast($article->image, '/');
        $ext      = pathinfo($filename, PATHINFO_EXTENSION);

        return Attachment::create([
            'name'          => Str::random(40),
            'original_name' => 'preview',
            'mime'          => 'image/webp',
            'extension'     => $ext,
            'size'          => 0,
            'path'          => 'articles/' . $article->id,
            'disk'          => 'public',
            'description'   => '',
            'alt'           => '',
            'user_id'       => auth()->id(),
        ]);
    }
}
