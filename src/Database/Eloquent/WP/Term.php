<?php

namespace Enlighten\Database\Eloquent\WP;

class Term extends AbstractModelWithMetadata
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'term_id';

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
    protected $metaType = 'term';

    /**
     * Relationship: Meta
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Enlighten\Database\Eloquent\WP\TermMeta', 'term_id');
    }
}
