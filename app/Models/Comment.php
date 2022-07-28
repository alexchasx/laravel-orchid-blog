<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;
    use Filterable;

    public $fillable = [
        'name',
        'email',
        'website',
        'user_id',
        'article_id',
        'content',
        'active',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы в даты.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'delete_at',
    ];

    /**
     * Возращает пользователя - владельца данного комментария
     *
     * @return belongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return belongsTo
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
