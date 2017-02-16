<?php

namespace Enlighten\Database\Eloquent\WP;

use Enlighten\Database\Eloquent\Model;

abstract class AbstractMeta extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The meta type for the model metadata.
     *
     * @var string
     */
    protected $metaType;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Attempt to save metadata using the Wordpress meta functions
        // in order to maintain integrations that rely on actions and filters
        static::saving(function($model){
            return $model->performMetadataUpdate();
        });
    }

    /**
     * Perform a metadata update operation.
     *
     * @return bool|null
     */
    public function performMetadataUpdate()
    {
        $func = "update_{$this->metaType}_meta";

        if (function_exists($func) && $this->isDirty('meta_value')) {
            $check = $func($this->{"{$this->metaType}_id"}, $this->meta_key, $this->meta_value);

            if ($check) {
                $this->syncOriginalAttribute('meta_value');
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Retrieves metadata for a term.
     *
     * @return mixed
     */
    public function getMetaValueAttribute()
    {
        return maybe_unserialize($this->attributes['meta_value']);
    }

    /**
     * Retrieves metadata for a term.
     *
     * @return mixed
     */
    public function setMetaValueAttribute($value)
    {
        $this->attributes['meta_value'] = maybe_serialize($value);
    }
}
