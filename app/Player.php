<?php
/**
 * Created by PhpStorm.
 * User: Ruben
 * Date: 23/09/2016
 * Time: 10:53
 */

namespace Project;


class Player extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'membership_id',
        'first_name',
        'last_name',
        'gender',
        'ranking_single',
        'ranking_double',
        'ranking_mix',
    ];

    // Relations

    public function teams() {
        return $this->belongsToMany('Project\Team', 'team_id');
    }
}