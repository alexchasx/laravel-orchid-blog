<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ContentImageUploadController extends Controller
{
    /** Максимальный размер файла (5 МБ). */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /** Разрешённые MIME-типы. */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Загрузка изображения для вставки в контент статьи.
     * Сохраняет WebP-вариант 16:9 в articles/{id}/content/{timestamp}.webp.
     */
    public function __invoke(Request $request, int $article): JsonResponse
    {
        $file = $request->file('file');

        if ($file === null || ! $file->isValid()) {
            return response()->json(['error' => 'Файл не указан или невалиден.'], 422);
        }

        // Проверка размера.
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json(['error' => 'Размер изображения превышает 5 МБ.'], 422);
        }

        // Проверка MIME.
        $mime = $file->getClientMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            return response()->json(['error' => 'Допустимы только JPEG, PNG или WebP.'], 422);
        }

        $contentDir = "articles/{$article}/content";
        $timestamp  = Str::slug(now()->format('Y-m-d-H-i-s'));
        $filename   = "{$timestamp}.webp";
        $path       = "{$contentDir}/{$filename}";

        // Конвертируем в WebP 16:9 cover, качество 80.
        $manager = new ImageManager(new Driver());
        $image   = $manager->read($file->get());
        $image   = $image->cover(1600, 900);
        $webp    = (string) $image->toWebp(80);

        Storage::disk('public')->put($path, $webp);

        // Абсолютный URL для вставки в markdown.
        $url = \App\Support\Seo::absoluteUrl(
            Storage::disk('public')->url($path)
        );

        return response()->json(['url' => $url]);
    }
}
