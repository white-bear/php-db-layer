<?php

namespace Cms\Db\Sql;


/**
 * Class Join
 * @package Cms\Db\Sql
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class Join
{
	const
		INNER               = 'INNER JOIN',
		CROSS               = 'CROSS JOIN',
		STRAIGHT            = 'STRAIGHT_JOIN',
		LEFT_OUTER          = 'LEFT OUTER JOIN',
		RIGHT_OUTER         = 'RIGHT OUTER JOIN',
		NATURAL_LEFT_OUTER  = 'NATURAL LEFT OUTER JOIN',
		NATURAL_RIGHT_OUTER = 'NATURAL RIGHT OUTER JOIN';

	static private $join_types = [
		self::INNER,
		self::CROSS,
		self::STRAIGHT,
		self::LEFT_OUTER,
		self::RIGHT_OUTER,
		self::NATURAL_LEFT_OUTER,
		self::NATURAL_RIGHT_OUTER,
	];

	private $type = self::INNER;
	private $table = '';
	private $on = [];
	private $on_params = [];
	private $params = [];


	/**
	 * @param string      $table
	 * @param string|null $type
	 */
	public function __construct($table, $type=null)
	{
		$this->table = $table;

		if ($type !== null && in_array($type, self::$join_types)) {
			$this->type = $type;
		}
	}

	/**
	 * @param       $condition
	 * @param array $params
	 *
	 * @return \Cms\Db\Sql\Join
	 */
	public function on($condition, array $params=[])
	{
		$this->on []= $condition;
		$this->on_params = array_merge($this->on_params, $params);

		return $this;
	}

	/**
	 * Получение параметров запроса
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Формирование результирующего SQL выражение
	 *
	 * @return string
	 */
	public function getSql()
	{
		$this->params = [];

		$sql = ' ' . $this->type;

		$sql .= ' ?i';
		$this->params []= $this->table;

		list($on, $params) = $this->getOnSql();
		if (! empty($on)) {
			$sql .= ' ON ' . $on;
			$this->params = array_merge($this->params, $params);
		}

		return $sql;
	}


	/**
	 * Формирование Sql части для условий отсечения записей
	 *
	 * @return array
	 */
	private function getOnSql()
	{
		return [join(' AND ', $this->on), $this->on_params];
	}
}
