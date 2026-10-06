<?php

namespace App\Orchid\Screens\Article;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Orchid\Layouts\Article\ArticleListTable;
use App\Orchid\Layouts\CreateOrUpdateArticle;
use App\Orchid\Layouts\Html;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class ArticleListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'articles' => Article::filters()->with('rubric')->defaultSort('id', 'desc')
                ->paginate(24),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Статьи';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать статью')->modal('createArticle')
                ->method('createOrUpdateArticle'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            ArticleListTable::class,

            Layout::modal('createArticle', CreateOrUpdateArticle::class)
                ->title('Создание статьи')
                ->size(Modal::SIZE_LG)
                ->applyButton('Создать'),

            Layout::modal('editArticle', CreateOrUpdateArticle::class)
                ->title('Редактирование статьи')
                ->size(Modal::SIZE_LG)
                ->async('asyncGetArticle'),

            new Html(),
        ];
    }

    public function asyncGetArticle(/* Article $article */): array
    {
        /*
         * В async-запросе Orchid параметр кнопки (article=<id>) передаётся в query-строке.
         * Из-за восстановления состояния экрана он не попадает ни в request()->query(),
         * ни в route-параметры, поэтому обычная инъекция Article $article даёт пустую модель.
         * Достаём id напрямую из сырой query-строки запроса.
         */
        parse_str((string) parse_url((string) request()->getRequestUri(), PHP_URL_QUERY), $query);
        $article = Article::with(['rubric', 'tags'])->findOrFail((int) ($query['article'] ?? 0));

        return [
            'article' => [
                'id'           => $article->id,
                'title'        => $article->title,
                'excerpt'        => $article->excerpt,
                'slug'         => $article->slug,
                'is_published' => $article->is_published,
                'rubric_id'    => $article->rubric_id,
                'tags'         => $article->tags->pluck('id')->all(),
                'published_at' => $article->published_at
                    ? \Illuminate\Support\Carbon::parse($article->published_at)->format('Y-m-d')
                    : null,
                'content_raw'  => $article->content_raw,
                'content_html' => $article->content_html,
                'meta_desc'    => $article->meta_desc,
                // Поле Picture для превью ожидает ссылку (relativeUrl), а не путь в storage.
                'image'        => $article->image
                    ? $this->toPublicRelativeUrl($article->image)
                    : null,
            ],
        ];
    }

    public function createOrUpdateArticle(ArticleRequest $request): void
    {
        $articleId = $request->input('article.id');
        // input() не извлекает файлы (они живут в $request->files), поэтому
        // берём значение из файлов, если поле пришло как upload.
        $image = $request->input('article.image') ?? $request->file('article.image');

        // Путь к изображению относительно диска public (null — изображения нет).
        $imagePath = null;

        // Прямая отправка файла (например, из API/тестов): сохраняем в storage/app/public/articles/.
        if ($image instanceof UploadedFile && $image->isValid()) {
            $imagePath = $image->store('articles', 'public');
        } elseif (is_string($image) && $image !== '') {
            // Picture-поле Orchid присылает строку: относительный путь (/storage/...),
            // полный URL или уже готовый путь (articles/...) — приводим к единому виду.
            $imagePath = $this->normalizeImagePath($image);
        }

        // Если изображение уже было — запоминаем путь, чтобы удалить файл
        // при замене ИЛИ очистке поля.
        $oldImagePath = null;
        if (!empty($articleId)) {
            $oldImagePath = Article::withoutGlobalScopes()->find($articleId)?->image;
        }

        $article = Article::updateOrCreate([
            'id' => $articleId,
        ], [
            'title' => $request->input('article.title'),
            'slug' => $request->input('article.slug'),
            'excerpt' => $request->input('article.excerpt'),
            'content_raw' => $request->input('article.content_raw'),
            // content_html не принимается из запроса — генерируется из content_raw в Article::booted().
            'user_id' => Auth::id(),
            'rubric_id' => $request->input('article.rubric_id'),
            'keywords' => $request->input('article.keywords'),
            'meta_desc' => $request->input('article.meta_desc'),
            'is_published' => $request->boolean('article.is_published'),
            'published_at' => $request->input('article.published_at'),
            'image' => $imagePath,
        ]);

        $article->tags()->sync($request->input('article.tags'));

        // Удаляем прежний файл, если он изменён (замена или очистка) и существует на диске.
        if ($oldImagePath && $oldImagePath !== $imagePath && Storage::disk('public')->exists($oldImagePath)) {
            Storage::disk('public')->delete($oldImagePath);
        }

        is_null($articleId) ? Toast::info('Статья создана') : Toast::info('Статья обновлена');
    }

    /**
     * Приводит значение поля Picture к пути относительно диска public.
     *
     * Поле Picture Orchid может прислать:
     *  - относительный путь вида "/storage/articles/x.jpg" (targetRelativeUrl);
     *  - полный URL вида "https://host/storage/articles/x.jpg" (targetUrl по умолчанию);
     *  - уже готовый путь "articles/x.jpg".
     * Все варианты нормализуются к "articles/x.jpg".
     */
    private function normalizeImagePath(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Полный URL → путь без хоста.
        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        // Отрезаем публичный префикс диска (по умолчанию /storage).
        $diskPath = rtrim((string) parse_url(
            (string) config('filesystems.disks.public.url'),
            PHP_URL_PATH
        ), '/') . '/';

        if ($diskPath !== '/' && str_starts_with($value, $diskPath)) {
            $value = substr($value, strlen($diskPath));
        } elseif (str_starts_with($value, '/')) {
            $value = ltrim($value, '/');
        }

        return $value === '' ? null : $value;
    }

    /**
     * Возвращает относительный URL файла для превью в поле Picture
     * (например, "/storage/articles/x.jpg"), отбрасывая хост.
     */
    private function toPublicRelativeUrl(string $path): ?string
    {
        $url = Storage::disk('public')->url($path);

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }
}
