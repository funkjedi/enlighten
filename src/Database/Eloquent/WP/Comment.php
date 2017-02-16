<?php

namespace Enlighten\Database\Eloquent\WP;

class Comment  extends AbstractModelWithMetadata
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'comment_ID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $metaType = 'comment';

    /**
     * Relationship: Post
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo('Enlighten\Database\Eloquent\WP\Post', 'post_id');
    }

    /**
     * Relationship: Meta
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\CommentMeta', 'comment_id');
    }
}