<?php

namespace Enlighten\Database\Eloquent\WP;

class User extends AbstractModelWithMetadata
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_pass',
        'user_activation_key',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $metaType = 'user';

    /**
     * Relationship: Posts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\Post', 'post_author');
    }

    /**
     * Relationship: Comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\Comment', 'user_id');
    }

    /**
     * Relationship: Meta
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\UserMeta', 'user_id');
    }
}