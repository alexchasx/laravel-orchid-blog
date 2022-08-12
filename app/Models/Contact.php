<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Support\Str;

class Contact extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    public $fillable = [
        'name',
        'email',
        'title',
        'message',
        'read',
    ];
}
