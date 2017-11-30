<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['referer'];

    /**
     * Не использовать автоматические поля created_at, deleted_at, предусмотренные молелью
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Метод ищет запись в таблице по массиву с параметрами
     *
     * @param array $param ['param' => $param]
     * @return Referer | null
     */
    public static function findByParam(array $param)
    {
        $refererBuilder = new Referer();
        return $refererBuilder->where($param)->first();
    }
}
