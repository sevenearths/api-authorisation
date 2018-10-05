<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'urls';

    /**
     * The attributes that are NOT mass assignable (excluding timestamps)
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Adding dynamic functions to the models
     *
     * @var array
     */
    protected $appends = array('group_name');

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Casts integers, floats and booleans to there native type for sending over JSON
     *
     * @var array
     */
    protected $casts = ['group_id' => 'integer', 'order' => 'integer', 'deny' => 'boolean'];

    /**
     * The users that belong to the group.
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }

    /**
     * The number of users that belong to the group.
     */
    public function getGroupNameAttribute()
    {
        return Group::findOrFail($this->group_id)->name;
    }

    public static function create(array $attributes = [])
    {
        $attributes['order'] = DB::table('urls')->where('group_id', $attributes['group_id'])->count();
        return parent::create($attributes);
    }

}