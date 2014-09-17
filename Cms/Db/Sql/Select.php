<?php

namespace Cms\Db\Sql;


/**
 * Реализация Sql select
 *
 * Пример построения сложного запроса
 *
 *	$query = $qb
 *		->select()
 *		->columns([['a' => 'm.id'], ['b' => 's.title']])
 *		->from(['m' => 'metrics'])
 *		->join($qb
 *			->join(['s' => 'services'])
 *			->on('?i = ?i', ['s.id', 'm.service_id'])
 *		)
 *		->where('?i = ?d', ['m.id', 1])
 * 		->orderBy(['-m.title']);
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
class Select extends BaseSql
{
	protected $tables = [];

	protected $select_distinct = self::SELECT_ALL;

	protected $no_cache = false;

	protected $calc_found_rows = false;
	protected $for_update = false;

	protected $group = [];
	protected $having = [];
	protected $having_params = [];


	/**
	 * Указание, в какую таблицу будет производиться вставка
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function into($table)
	{
		if (! empty($table)) {
			$this->tables []= $table;
		}

		return $this;
	}

	/**
	 * Выбор только уникальных результатов
	 *
	 * @param  string $distinct
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function distinct($distinct='distinct')
	{
		if ($distinct == 'all') {
			$this->select_distinct = self::SELECT_ALL;
		}
		elseif ($distinct == 'distinct') {
			$this->select_distinct = self::SELECT_DISTINCT;
		}
		elseif ($distinct == 'distinctrow') {
			$this->select_distinct = self::SELECT_DISTINCTROW;
		}

		return $this;
	}

	/**
	 * Выполнение запроса с отключенным кешем на уровне БД
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function noCache()
	{
		$this->no_cache = true;

		return $this;
	}

	/**
	 * Выбор количества строк, соответствующих указанным условиям
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function calcFoundRows()
	{
		$this->calc_found_rows = true;

		return $this;
	}

	/**
	 * Изменение приоритета запроса
	 *
	 * @param  string $priority
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function priority($priority='high')
	{
		if ($priority == 'high') {
			$this->priority = self::PRIORITY_HIGH;
		}

		return $this;
	}

	/**
	 * Блокирование записей на чтение
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function forUpdate()
	{
		$this->for_update = true;

		return $this;
	}

	/**
	 * Группировка записей
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function groupBy(array $columns=[])
	{
		$this->group = $columns;

		return $this;
	}

	/**
	 * Формирование Sql части для группировки записей
	 *
	 * @return array
	 */
	protected function getGroupBySql()
	{
		if (empty($this->group)) {
			return ['', []];
		}

		$parts = [];
		$params = [];
		foreach ($this->group as $key => $val) {
			if (is_int($key)) {
				$parts []= '?i';
				$params []= $val;
			}
			elseif (is_string($key)) {
				$order = 'ASC';
				if (strtolower($val) == 'desc') {
					$order = 'DESC';
				}

				$parts []= '?i ' . $order;
				$params []= $key;
			}
		}

		return [
			'GROUP BY ' . join(', ', $parts),
			$params
		];
	}

	/**
	 * Условия для отсечения по аггрегациям
	 *
	 * @param  string $having
	 * @param  array  $params
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function having($having, array $params=[])
	{
		$this->having []= $having;
		$this->having_params = array_merge($this->having_params, $params);

		return $this;
	}

	/**
	 * Формирование Sql части для отсечения по аггрегациям записей
	 *
	 * @return array
	 */
	protected function getHavingSql()
	{
		return [join(' AND ', $this->having), $this->having_params];
	}

	/**
	 * Формирование результирующего SQL выражение
	 *
	 * @return string
	 */
	public function getSql()
	{
		$this->params = [];

		$sql = 'SELECT ' . $this->select_distinct;

		$sql .= ' ' . $this->priority;

		if ($this->no_cache) {
			$sql .= ' SQL_NO_CACHE';
		}

		if ($this->calc_found_rows) {
			$sql .= ' SQL_CALC_FOUND_ROWS';
		}

		$sql .= ' ?r';
		$this->params []= empty($this->columns) ? ['*'] : $this->columns;

		$sql .= ' FROM ?r';
		$this->params []= $this->tables;

		if (count($this->join)) {
			foreach ($this->join as $join) {
				$sql .= $join->getSql();
				$this->params = array_merge($this->params, $join->getParams());
			}
		}

		list($where, $params) = $this->getWhereSql();
		if (! empty($where)) {
			$sql .= ' WHERE ' . $where;
			$this->params = array_merge($this->params, $params);
		}

		list($group_by, $params) = $this->getGroupBySql();
		if (! empty($group_by)) {
			$sql .= ' ' . $group_by;
			$this->params = array_merge($this->params, $params);
		}

		list($having, $params) = $this->getHavingSql();
		if (! empty($having)) {
			$sql .= ' HAVING ' . $having;
			$this->params = array_merge($this->params, $params);
		}

		list($order_by, $params) = $this->getOrderBySql();
		if (! empty($order_by)) {
			$sql .= ' ' . $order_by;
			$this->params = array_merge($this->params, $params);
		}

		list($limit, $params) = $this->getLimitSql();
		if (! empty($limit)) {
			$sql .= ' ' . $limit;
			$this->params = array_merge($this->params, $params);
		}

		if ($this->for_update) {
			$sql .= ' FOR UPDATE';
		}

		return $sql;
	}
}
