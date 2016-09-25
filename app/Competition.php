<?php

namespace Project;

class Competition extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'tracking_id',
        'name',
    ];

    // Relations

    public function draws() {
        return $this->hasMany('Project\Draw', 'competition_id');
    }
}