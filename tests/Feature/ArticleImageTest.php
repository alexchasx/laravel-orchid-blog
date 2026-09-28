<?php

namespace Tests\Feature;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use App\Orchid\Screens\Article\ArticleListScreen;
use App\Services\ArticleImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Models\Attachment;
use Tests\TestCase;

class ArticleImageTest extends TestCase
{
    use RefreshDatabase;

    /** Максимальный размер загружаемого файла (5 МБ) — дублирует константу сервиса. */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('sitemap.xml');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->permissions = ['platform.index' => true];
        $admin->save();

        return $admin;
    }

    /**
     * Генерирует валидный PNG-файл (небольшой, чтобы конвертация в тесте была быстрой).
     */
    private function pngBytes(int $width = 120, int $height = 90): string
    {
        $img = imagecreatetruecolor($width, $height);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 90, 180));

        ob_start();
        imagepng($img);
        $data = (string) ob_get_clean();
        imagedestroy($img);

        return $data;
    }

    /**
     * Создаёт запись Attachment и кладёт файл на диск public по пути,
     * который ожидает Attachment::physicalPath() (path/name.extension).
     */
    private function makeAttachment(
        User $user,
        string $fileName,
        string $mime,
        string $extension,
        string $content,
        string $path = 'tmp/'
    ): Attachment {
        $attachment = Attachment::create([
            'name'          => pathinfo($fileName, PATHINFO_FILENAME),
            'original_name' => $fileName,
            'mime'          => $mime,
            'extension'     => $extension,
            'size'          => strlen($content),
            // Orchid хранит path с завершающим слэшем: physicalPath() = path.name.ext.
            'path'          => $path,
            'disk'          => 'public',
            'description'   => '',
            'alt'           => '',
            'user_id'       => $user->id,
        ]);

        Storage::disk('public')->put(
            "{$path}{$attachment->name}.{$attachment->extension}",
            $content
        );

        return $attachment;
    }

    /**
     * Вызывает метод экрана напрямую с сформированным запросом модалки
     * (поля вида article[title], article[image] и т.п.).
     */
    private function callCreateOrUpdate(array $payload): void
    {
        $request = ArticleRequest::create('/admin/articles', 'POST', $payload);

        (new ArticleListScreen())->createOrUpdateArticle($request, app(ArticleImageService::class));
    }

    /**
     * Базовый payload модалки для обновления статьи.
     * Поля из $extra['article'] добавляются/переопределяют базовые
     * (глубокое слияние вложенного массива article).
     */
    private function articlePayload(Article $article, Rubric $rubric, array $extra = []): array
    {
        $fields = [
            'id'           => $article->id,
            'title'        => $article->title,
            'slug'         => null,
            'rubric_id'    => $rubric->id,
            'tags'         => [],
            'content_raw'  => 'Контент статьи.',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
            'keywords'     => '',
            'meta_desc'    => '',
        ];

        if (isset($extra['article']) && is_array($extra['article'])) {
            $fields = array_merge($fields, $extra['article']);
        }

        return ['article' => $fields];
    }

    /**
     * Опубликованная статья с рубрикой, автором и тегом.
     */
    private function createPublishedArticle(array $attributes = []): Article
    {
        $admin = $this->admin();
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create(array_merge([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ], $attributes));

        $article->tags()->attach($tag->id);

        return $article;
    }

    /* ------------------------------------------------------------------
     * Загрузка изображения
     * ------------------------------------------------------------------ */

    public function test_store_creates_all_variants_and_sets_medium_path(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'title'        => 'Статья с изображением',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
        ]);
        $attachment = $this->makeAttachment($admin, 'hero.png', 'image/png', 'png', $this->pngBytes());

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => [
                'image'     => [$attachment->id],
                'image_alt' => 'Альт-текст к изображению',
            ],
        ]));

        $article->refresh();
        $dir = "articles/{$article->id}";

        // Оригинал + три WebP-варианта 16:9.
        Storage::disk('public')->assertExists("{$dir}/original.png");
        Storage::disk('public')->assertExists("{$dir}/large.webp");
        Storage::disk('public')->assertExists("{$dir}/medium.webp");
        Storage::disk('public')->assertExists("{$dir}/thumbnail.webp");

        // В поле image хранится путь к medium.webp.
        $this->assertSame("{$dir}/medium.webp", $article->image);
        $this->assertSame('Альт-текст к изображению', $article->image_alt);

        // Вложение из таблицы attachments удаляется после обработки.
        $this->assertNull(Attachment::find($attachment->id));
    }

    public function test_invalid_mime_type_is_not_saved(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'title'        => 'Статья с невалидным файлом',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
        ]);
        // Текстовый файл с mime text/plain — не проходит проверку типа.
        $attachment = $this->makeAttachment(
            $admin,
            'notes.txt',
            'text/plain',
            'txt',
            'Это просто текстовый файл, а не изображение.'
        );

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => ['image' => [$attachment->id]],
        ]));

        $article->refresh();

        $this->assertNull($article->image);
        Storage::disk('public')->assertMissing("articles/{$article->id}/medium.webp");
        // При ошибке вложение не удаляется.
        $this->assertNotNull(Attachment::find($attachment->id));
    }

    public function test_oversized_file_is_not_saved(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'title'        => 'Статья с большим файлом',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
        ]);
        // Файл больше 5 МБ — проверка размера срабатывает раньше проверки содержимого.
        $huge = str_repeat('x', self::MAX_FILE_SIZE + 1);
        $attachment = $this->makeAttachment($admin, 'huge.png', 'image/png', 'png', $huge);

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => ['image' => [$attachment->id]],
        ]));

        $article->refresh();

        $this->assertNull($article->image);
        Storage::disk('public')->assertMissing("articles/{$article->id}/medium.webp");
        $this->assertNotNull(Attachment::find($attachment->id));
    }

    /* ------------------------------------------------------------------
     * Замена изображения
     * ------------------------------------------------------------------ */

    public function test_replacing_image_removes_old_files_and_creates_new(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'title'        => 'Статья со сменой картинки',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
        ]);
        $first = $this->makeAttachment($admin, 'first.png', 'image/png', 'png', $this->pngBytes());

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => [
                'image'     => [$first->id],
                'image_alt' => 'Первая картинка',
            ],
        ]));

        $article->refresh();
        $dir = "articles/{$article->id}";
        Storage::disk('public')->assertExists("{$dir}/medium.webp");

        // Маркерный файл: при замене папка пересоздаётся целиком → маркер исчезнет.
        Storage::disk('public')->put("{$dir}/stale-marker.txt", 'старый файл');

        $second = $this->makeAttachment($admin, 'second.png', 'image/png', 'png', $this->pngBytes());

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => [
                'image'     => [$second->id],
                'image_alt' => 'Вторая картинка',
            ],
        ]));

        $article->refresh();

        // Старые файлы удалены (вся папка пересоздана), новые варианты на месте.
        Storage::disk('public')->assertMissing("{$dir}/stale-marker.txt");
        Storage::disk('public')->assertExists("{$dir}/original.png");
        Storage::disk('public')->assertExists("{$dir}/large.webp");
        Storage::disk('public')->assertExists("{$dir}/medium.webp");
        Storage::disk('public')->assertExists("{$dir}/thumbnail.webp");

        $this->assertSame("{$dir}/medium.webp", $article->image);
        $this->assertSame('Вторая картинка', $article->image_alt);
        $this->assertNull(Attachment::find($second->id));
    }

    /* ------------------------------------------------------------------
     * Удаление файлов при жёстком удалении статьи
     * ------------------------------------------------------------------ */

    public function test_force_delete_removes_image_directory(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'user_id'      => $admin->id,
            'rubric_id'    => $rubric->id,
            'title'        => 'Статья для жёсткого удаления',
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
        ]);
        $attachment = $this->makeAttachment($admin, 'hero.png', 'image/png', 'png', $this->pngBytes());

        $this->callCreateOrUpdate($this->articlePayload($article, $rubric, [
            'article' => ['image' => [$attachment->id]],
        ]));

        $article->refresh();
        $dir = "articles/{$article->id}";
        Storage::disk('public')->assertExists("{$dir}/medium.webp");

        $article->forceDelete();

        Storage::disk('public')->assertMissing("{$dir}/medium.webp");
        Storage::disk('public')->assertMissing("{$dir}/original.png");
        $this->assertFalse(Storage::disk('public')->directoryExists($dir));
    }

    /* ------------------------------------------------------------------
     * OG / Twitter и fallback на seo.og_image
     * ------------------------------------------------------------------ */

    public function test_article_page_og_image_uses_thumbnail_variant(): void
    {
        Storage::fake('public');
        $article = $this->createPublishedArticle(['title' => 'Статья для OG-разметки']);
        $dir = "articles/{$article->id}";
        $article->update(['image' => "{$dir}/medium.webp"]);
        Storage::disk('public')->put("{$dir}/medium.webp", 'image');
        Storage::disk('public')->put("{$dir}/thumbnail.webp", 'image');

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $content = $response->getContent();
        $appUrl = config('app.url');
        $this->assertStringContainsString(
            'property="og:image" content="' . $appUrl . '/storage/' . $dir . '/thumbnail.webp"',
            $content
        );
    }

    public function test_article_page_without_image_falls_back_to_default_og_image(): void
    {
        config(['seo.og_image' => '/images/default-og.png']);
        $article = $this->createPublishedArticle([
            'title' => 'Статья без изображения',
        ]);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $content = $response->getContent();
        $appUrl = config('app.url');
        $this->assertStringContainsString(
            'property="og:image" content="' . $appUrl . '/images/default-og.png"',
            $content
        );
        $this->assertStringNotContainsString('thumbnail.webp', $content);
    }

    /* ------------------------------------------------------------------
     * Sitemap: image:url указывает на thumbnail.webp
     * ------------------------------------------------------------------ */

    public function test_sitemap_image_url_points_to_thumbnail_variant(): void
    {
        Storage::fake('public');
        $article = $this->createPublishedArticle([
            'title'     => 'Статья для sitemap',
            'image_alt' => 'Описание изображения для sitemap',
        ]);
        $dir = "articles/{$article->id}";
        $article->update(['image' => "{$dir}/medium.webp"]);
        // Контроллер включает <image:image>, только если medium-файл существует на диске.
        Storage::disk('public')->put("{$dir}/medium.webp", 'image');

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();
        $appUrl = config('app.url');
        $this->assertStringContainsString(
            '<image:url>' . $appUrl . '/storage/' . $dir . '/thumbnail.webp</image:url>',
            $content
        );
        $this->assertStringContainsString(
            '<image:caption>Описание изображения для sitemap</image:caption>',
            $content
        );
    }
}
