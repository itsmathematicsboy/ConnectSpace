<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo accounts (password: password)
        $users = [
            ['name' => 'Andi Pratama',  'email' => 'andi@friendster.test',  'bio' => 'Frontend Developer. Suka kopi dan musik indie.'],
            ['name' => 'Bunga Lestari', 'email' => 'bunga@friendster.test', 'bio' => 'Product Designer di tim Design System.'],
            ['name' => 'Citra Dewi',    'email' => 'citra@friendster.test', 'bio' => 'Backend Engineer. Pencinta kerupuk & REST API.'],
        ];

        $userIds = [];
        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password'),
                'bio' => $u['bio'],
                'avatar' => null,
            ]);
            $userIds[] = $user->id;
        }

        // Sample posts
        $posts = [
            ['user_id' => $userIds[0], 'content' => 'Halo Friendster! Hari ini saya lagi belajar Laravel + Blade untuk UJK. Semoga lancar! 🚀'],
            ['user_id' => $userIds[1], 'content' => 'Reminder: Design review jam 3 sore ya. Jangan lupa bawa prototype terbaru.'],
            ['user_id' => $userIds[2], 'content' => 'Tip database: selalu tambah UNIQUE constraint untuk mencegah duplikasi like. Lebih aman daripada cek di level aplikasi saja. REST API.'],
        ];

        $postIds = [];

        foreach ($posts as $p) {
            $post = Post::create($p);
            $postIds[] = $post->id;
        }

        // Sample like (Andi likes Citra's post)
        Like::create([
            'user_id' => $userIds[0],
            'post_id' => $postIds[2],
        ]);

        // Sample comments
        Comment::create([
            'post_id' => $postIds[0],
            'user_id' => $userIds[1],
            'comment' => 'Semangat, Andi! Laravel itu asik banget kalau udah paham konsep MVC-nya.',
        ]);

        Comment::create([
            'post_id' => $postIds[0],
            'user_id' => $userIds[2],
            'comment' => 'Setuju, Blade templating-nya juga elegant. Untuk UJK pasti cukup.',
        ]);
    }
}