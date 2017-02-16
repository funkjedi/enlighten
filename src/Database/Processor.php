<?php

namespace Enlighten\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\MySqlProcessor;

class Processor extends MySqlProcessor
{
	/**
	 * Process an  "insert get ID" query.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @param  string  $sql
	 * @param  array   $values
	 * @param  string  $sequence
	 * @return int
	 */
	public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
	{
		$query->getConnection()->insert($sql, $values);

		$id = $query->getConnection()->getWpdb()->insert_id;

		return is_numeric($id) ? (int) $id : $id;
	}
}
