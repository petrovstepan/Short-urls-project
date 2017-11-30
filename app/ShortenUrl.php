<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShortenUrl extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hash', 'url'];

    /**
     * Не использовать автоматические поля created_at, deleted_at, предусмотренные молелью
     *
     * @var array
     */
    public $timestamps = false;


    /**
     * Метод устанавливает связь с моделю User
     * Свзязь вида thistable.id = anothertable.thistable_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function askingUser()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Метод устанавливает связь с моделю RefererUrlRelation
     * Свзязь вида thistable.id = anothertable.thistable_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function follows()
    {
        return $this->hasMany('App\RefererUrlRelation', 'url_id', 'id');
    }

    /**
     * Метод ищет запись в таблице по массиву с параметрами
     *
     * @param array $param ['param' => $param]
     * @return ShortenUrl | null
     */
    public static function findByParam(array $param)
    {
        $urlBuilder = new ShortenUrl();
        return $urlBuilder->where($param)->first();
    }
}
