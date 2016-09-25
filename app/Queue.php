<?php


namespace Project;


class Queue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue';

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'route',
    ];

    public static function createJob($route) {
        self::create([
            'route' => $route,
        ]);
    }
}