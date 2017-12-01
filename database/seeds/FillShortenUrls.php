<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FillShortenUrls extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            'http://php.net',
            'https://laravel.com',
            'https://vk.com/petrovstepan',
            'https://stackoverflow.com',
            'https://www.apple.com',
            'https://translate.google.com',
            'https://twitter.com',
            'https://www.youtube.com',
            'https://tinyurl.com',
            'http://materializecss.com',
            'https://yandex.ru',
            'http://lmgtfy.com/?q=tinyurl',
            'https://www.amazon.com',
            'http://www.sberbank.ru/ru/person',
            'https://www.coursera.org',
            'https://openedu.ru',
            'https://perm.hse.ru/ma/pm/',
            'https://www.facebook.com',
            'http://fanserials.com',
            'https://rutracker.org/forum/index.php',
            'https://ru.wikipedia.org/wiki/REST',
            'https://www.xsolla.com/ru/'
        ];

        foreach ($array as $value)
        {
            DB::table('shorten_urls')->insert([
                'url' => $value,
                'hash' => Str::random(8),
                'user_id' => rand(1, 3)
            ]);
        }

        DB::table('shorten_urls')->insert([
            'url' => 'https://tinyurl.com/y9ms7g7e',
            'hash' => 'tinyurl',
            'user_id' => 1
        ]);
    }
}
