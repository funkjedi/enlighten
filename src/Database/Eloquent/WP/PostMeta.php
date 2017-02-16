<?php

namespace Enlighten\Database\Eloquent\WP;

class PostMeta extends AbstractMeta
{
    /**
     * The meta type for the model metadata.
     *
     * @var string
     */
    protected $metaType = 'post';

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'postmeta';
    }

    /**
     * Relationship: Post
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo('Enlighten\Database\Eloquent\WP\Post', 'post_id');
    }
}
