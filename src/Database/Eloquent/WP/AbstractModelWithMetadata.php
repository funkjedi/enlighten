<?php

namespace Enlighten\Database\Eloquent\WP;

use Enlighten\Database\Eloquent\Model;

abstract class AbstractModelWithMetadata extends Model
{
    /**
     * The meta type for the model metadata.
     *
     * @var string
     */
    protected $metaType;

    /**
     * Indicates if the metadata should be appended by default.
     *
     * @var bool
     */
    protected $appendMetadata = true;

    /**
     * The model's meta.
     *
     * @var array
     */
    protected $metadata = array();

    /**
     * The original state for the model meta.
     *
     * @var array
     */
    protected $originalMetadata = array();

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = array(), $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);

        $model->hydrateMetadata();

        return $model;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function($model){
            $model->performMetadataUpdate();
        });
    }

    /**
     * Retrieve metadata associated with the model.
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Hydrate model with metadata.
     *
     * @return bool|null
     */
    public function hydrateMetadata()
    {
        $func = "get_{$this->metaType}_meta";

        if (function_exists($func)) {
            $this->metadata = $func($this->getKey());

            foreach ($this->metadata as $key => &$value) {
                if (count($value) === 1) {
                    $value = maybe_unserialize($value[0]);
                    $value = $value === '' ? null : $value;
                } else {
                    array_walk($value, function(&$value){
                        $value = maybe_unserialize($value);
                        $value = $value === '' ? null : $value;
                    });
                }
            }

            $this->syncOriginalMetadata();
        }
    }

    /**
     * Perform a metadata update operation.
     *
     * @return bool|null
     */
    public function performMetadataUpdate()
    {
        $func = "update_{$this->metaType}_meta";

        if (function_exists($func)) {
            foreach ($this->getDirtyMetadata() as $key => $value) {
                $func($this->getKey(), $key, $value);
            }

            $this->syncOriginalMetadata();
        }
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginalMetadata()
    {
        $this->originalMetadata = $this->metadata;

        return $this;
    }

    /**
     * Get the metadata that have been changed since last sync.
     *
     * @return array
     */
    public function getDirtyMetadata()
    {
        $dirty = array();

        foreach ($this->metadata as $key => $value) {
            if (!array_key_exists($key, $this->originalMetadata)) {
                $dirty[$key] = $value;
            }
            elseif ($value !== $this->originalMetadata[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return parent::hasGetMutator($key) || array_key_exists($key, $this->metadata);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $this->metadata[$key]);
        }

        return $this->metadata[$key];
    }

    /**
     * Set a given metadata attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setMetadataAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            $method = 'set'.studly_case($key).'Attribute';

            return $this->{$method}($value);
        }

        $this->metadata[$key] = $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->metadata)) {
            return $this->setMetadataAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Delete a given metadata attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function deleteMetadataAttribute($key, $value = null)
    {
        $func = "delete_{$this->metaType}_meta";

        if (function_exists($func)) {
            $func($this->getKey(), $key, $value);

            array_forget($this->metadata, $key);
            $this->syncOriginalMetadata();
        }
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getArrayableAppends()
    {
        $appends = array();

        if ($this->appendMetadata) {
            $appends = array_keys($this->getMetadata());
            $appends = $this->getArrayableItems(array_combine($appends, $appends));
        }

        return array_merge(parent::getArrayableAppends(), $appends);
    }
}
