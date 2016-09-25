<?php

namespace Project;

class Club extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'tracking_id',
        'name',
        'address',
        'contact_person',
        'tel',
        'email',
        'website',
    ];
}