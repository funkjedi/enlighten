<?php

namespace Enlighten\Database\Eloquent\WP;

class Post extends AbstractModelWithMetadata
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'post_password',
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'post_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'post_modified';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $metaType = 'post';

    /**
     * Scope: Filter by post type
     *
     * @param $query
     * @param string $type
     *
     * @return mixed
     */
    public function scopeType($query, $type = 'post')
    {
        return $query->where('post_type', '=', $type);
    }

    /**
     * Scope: Filter by post status
     *
     * @param $query
     * @param string $status
     *
     * @return mixed
     */
    public function scopeStatus($query, $status = 'publish')
    {
        return $query->where('post_status', '=', $status);
    }

    /**
     * Scope: Filter by post author
     *
     * @param $query
     * @param null $author
     *
     * @return mixed
     */
    public function scopeAuthor($query, $author = null)
    {
        if ($author) {
            return $query->where('post_author', '=', $author);
        }
    }

    /**
     * Relationship: Author
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('Enlighten\Database\Eloquent\WP\User', 'post_author');
    }

    /**
     * Relationship: Comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\Comment', 'comment_post_ID');
    }

    /**
     * Relationship: Meta
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\PostMeta', 'post_id');
    }
}