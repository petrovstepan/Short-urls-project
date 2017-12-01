<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FillUsers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = ['Admin', 'Alex', 'John'];

        foreach ($array as $value)
        {
            DB::table('users')->insert([
                'name' => $value,
                'email' => strtolower("$value@ya.com"),
                'password' => Hash::make("{$value}pass")
            ]);
        }


    }
}
