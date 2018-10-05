<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    /**
     * Adding dynamic functions to the models
     *
     * @var array
     */
    protected $appends = array('users_count', 'urls_count');

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The users that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    /**
     * Get the urls for a group
     */
    public function urls()
    {
        return $this->hasMany('App\Models\Url')->orderBy('order');
    }

    /**
     * The number of users that belong to the group.
     */
    public function getUsersCountAttribute()
    {
        return $this->belongsToMany('App\Models\User')->count();
    }

    /**
     * The number of urls that belong to the group.
     */
    public function getUrlsCountAttribute()
    {
        return DB::table('urls')->where('group_id', $this->id)->count();
    }
}