<?php

use Illuminate\Database\Seeder;

class FillRefererUrlRelationsTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $today = time();
        $start = $today - 10 * 24 * 3600;

        foreach (range(0,10000) as $value)
        {
            do
            {
                $referer_id = rand(1, 23);
                $url_id = rand(1, 23);

            } while ($referer_id === $url_id);


            DB::table('referer_url_relations')->insert([
                'referer_id' => $referer_id,
                'url_id' => $url_id,
                'timestamp' => date('Y-m-d H-i-s', rand($start, $today))
            ]);
        }
    }
}
