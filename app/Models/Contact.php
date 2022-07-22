<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Contact extends Model
{
    use HasFactory;
    use AsSource;

    public $fillable = [
        'name',
        'email',
        'title',
        'message',
        'read',
    ];
}
