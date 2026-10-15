<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use App\Orchid\Screens\Tag\TagListScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagListScreenTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->permissions = ['platform.index' => true];
        $admin->save();

        return $admin;
    }

    /**
     * Вызывает метод createOrUpdateTag экрана напрямую.
     */
    private function callCreateOrUpdate(array $payload): void
    {
        $request = Request::create('/nexus/tags', 'POST', $payload);

        (new TagListScreen())->createOrUpdateTag($request);
    }

    public function test_create_tag_without_description_gets_template_description(): void
    {
        $this->actingAs($this->admin());

        $this->callCreateOrUpdate([
            'tag' => [
                'id' => null,
                'title' => 'Laravel',
                'description' => null,
                'slug' => null,
                'popular' => 0,
                'active' => true,
            ],
        ]);

        $tag = Tag::where('title', 'Laravel')->first();

        $this->assertNotNull($tag);
        $this->assertSame(
            'Статьи по тегу «Laravel»: обзоры, гайды, примеры кода и не только',
            $tag->description
        );
    }

    public function test_create_tag_with_custom_description_keeps_it(): void
    {
        $this->actingAs($this->admin());

        $this->callCreateOrUpdate([
            'tag' => [
                'id' => null,
                'title' => 'PHP',
                'description' => 'Всё о PHP',
                'slug' => null,
                'popular' => 0,
                'active' => true,
            ],
        ]);

        $tag = Tag::where('title', 'PHP')->first();

        $this->assertNotNull($tag);
        $this->assertSame('Всё о PHP', $tag->description);
    }

    public function test_update_tag_does_not_overwrite_description(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $tag = Tag::factory()->create([
            'title' => 'Old Title',
            'description' => 'Своё описание',
            'active' => true,
        ]);

        $this->callCreateOrUpdate([
            'tag' => [
                'id' => $tag->id,
                'title' => 'New Title',
                'description' => $tag->description,
                'slug' => null,
                'popular' => 0,
                'active' => true,
            ],
        ]);

        $tag->refresh();

        $this->assertSame('New Title', $tag->title);
        $this->assertSame('Своё описание', $tag->description);
    }

    public function test_tag_validation_rejects_too_long_title(): void
    {
        $rules = [
            'tag.title' => ['required', 'min:2', 'max:254'],
            'tag.description' => ['nullable', 'string', 'max:500'],
            'tag.slug' => ['nullable', 'string', 'max:254', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'tag.popular' => ['min:0', 'numeric'],
            'tag.active' => ['required', 'boolean'],
        ];

        $v = Validator::make([
            'tag' => [
                'id' => null,
                'title' => str_repeat('a', 255),
                'description' => null,
                'slug' => null,
                'popular' => 0,
                'active' => true,
            ],
        ], $rules);

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('tag.title', $v->errors()->toArray());
    }
}
