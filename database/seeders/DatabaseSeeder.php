<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();
        AdminUser::factory(1)->create([
            'name' => 'admin',
            'email' => 'laravel@laravel.com',
            'password' => bcrypt('1234'),
        ]);
        Category::factory(8)->create();
        Article::factory(16)->create();
    }
}
