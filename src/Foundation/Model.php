<?php

namespace Enlighten\Foundation;

use Ernix\LetterCase;

abstract class Eloquent
{
	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Create an instance from an object.
	 *
	 * @return \App\Model
	 */
	public static function createFromObject($object)
	{
		if (is_object($object)) {
			return (new self)->setRawAttributes(get_object_vars($object));
		}
	}

	/**
	 * Set the array of model attributes. No checking is done.
	 *
	 * @param array
	 * @return $this
	 */
	public function setRawAttributes(array $attributes)
	{
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * Get an attribute from the model.
	 *
	 * @param string
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
			// If the attribute has a get mutator, we will call that then return what
			// it returns as the value, which is useful for transforming values on
			// retrieval from the model to a form that is more useful for usage.
			if ($this->hasGetMutator($key)) {
				return $this->mutateAttribute($key, $this->attributes[$key]);
			}

			return $this->attributes[$key];
		}
	}

	/**
	 * Determine if a get mutator exists for an attribute.
	 *
	 * @param string
	 * @return bool
	 */
	public function hasGetMutator($key)
	{
		return method_exists($this, 'get'.LetterCase::pascal($key).'Attribute');
	}

	/**
	 * Get the value of an attribute using its mutator.
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	protected function mutateAttribute($key, $value)
	{
		return $this->{'get'.LetterCase::pascal($key).'Attribute'}($value);
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param string
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getAttribute($key);
	}

	/**
	 * Dynamically set attributes on the model.
	 *
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}

	/**
	 * Determine if an attribute or relation exists on the model.
	 *
	 * @param string
	 * @return bool
	 */
	public function __isset($key)
	{
		return !is_null($this->getAttribute($key));
	}

	/**
	 * Unset an attribute on the model.
	 *
	 * @param string
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->attributes[$key]);
	}
}
