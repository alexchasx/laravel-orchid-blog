<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

class Rubrics extends Model
{
    use HasFactory;
    use AsSource;
    use SoftDeletes;

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
    public function articles()
    {
        return $this->hasMany(Article::class, 'rubric_id');
    }
}