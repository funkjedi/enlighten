<?php

namespace Enlighten\Database\Eloquent\WP;

class UserMeta extends AbstractMeta
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * The meta type for the model metadata.
     *
     * @var string
     */
    protected $metaType = 'user';

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return 'usermeta';
    }

    /**
     * Relationship: User
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Enlighten\Database\Eloquent\WP\User', 'user_id');
    }
}
