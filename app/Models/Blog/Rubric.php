<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Rubric extends Model
{
    use HasFactory;
    use AsSource;

    /**
     * Определяет необходимость отметок времени для модели.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Атрибуты, для которых запрещено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'slug',
        'title',
        'description',
    ];

    /**
     * Возращает все статьи к данной категории.
     */
    public function posts()
    {
        return $this->hasMany(Article::class, 'rubric_id');
    }
}
