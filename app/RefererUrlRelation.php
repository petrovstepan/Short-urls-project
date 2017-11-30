<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RefererUrlRelation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url_id', 'referer_id'];

    /**
     * Не использовать автоматические поля created_at, deleted_at, предусмотренные молелью
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Метод возвращает коллекцию массивов с отсортированным количеством переходов за определенный период времени для конкретной ссылки
     *
     * @param int $url_id
     * @param int $sort
     * @param timestamp $from_date
     * @param timestamp $to_date
     *
     * @return Collection [arrays]
     */
    public static function getGroupedRedirects($url_id, $sort, $from_date, $to_date)
    {
        $groupedRedirects = DB::table(
            DB::raw(
                "(SELECT * 
                FROM referer_url_relations 
                WHERE url_id = ? 
                AND UNIX_TIMESTAMP(timestamp) >= ? 
                AND UNIX_TIMESTAMP(timestamp) <= ?) tab"
            ))
            ->addBinding($url_id)
            ->addBinding($from_date)
            ->addBinding($to_date)
            ->select(
                DB::raw("UNIX_TIMESTAMP(timestamp) - (UNIX_TIMESTAMP(timestamp) % ?) as time"),
                DB::raw("COUNT(UNIX_TIMESTAMP(timestamp) - (UNIX_TIMESTAMP(timestamp) % ?)) as num"
                ))
            ->addBinding($sort, 'select')
            ->addBinding($sort, 'select')
            ->groupBy('time')
            ->get();

        return $groupedRedirects;
    }

    /**
     * Метод возвращает коллекцию массивов с топом источников переходов для определенной ссылке
     *
     * @param int $url_id
     * @param int $limit
     *
     * @return Collection [arrays]
     */
    public static function getTopRedirects($url_id, $limit = 20)
    {
        $topRedirects =DB::table('referer_url_relations')
            ->select('referer', DB::raw('COUNT(referer_id) as num'))
            ->whereUrlId($url_id)
            ->join('referers', 'referers.id', '=', 'referer_url_relations.referer_id')
            ->groupBy('referer_id')
            ->limit($limit)
            ->orderBy('num', 'desc')
            ->get();

        return $topRedirects;
    }
}
