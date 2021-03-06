<?php

use Illuminate\Database\Seeder;

class FillReferersTable extends Seeder
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
            DB::table('referers')->insert([
                'referer' => $value,
            ]);
        }
    }
}
