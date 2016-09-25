<?php

namespace Project;


class Draw extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'tracking_id',
        'competition_id',
        'name',
    ];

    // Relations

    public function competition() {
        return $this->belongsTo('Project\Competition', 'competition_id');
    }

    public function matches() {
        return $this->hasMany('Project\Match', 'draw_id');
    }
}