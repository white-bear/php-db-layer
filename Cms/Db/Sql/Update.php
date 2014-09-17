<?php

namespace Cms\Db\Sql;


/**
 * Реализация Sql update
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
class Update extends BaseSql
{
	/**
	 * Формирование результирующего SQL выражение
	 *
	 * @return string
	 */
	public function getSql()
	{
		$this->params = [];

		$sql = 'UPDATE ' . $this->priority;
		if ($this->ignore) {
			$sql .= ' IGNORE';
		}

		$sql .= ' ?i';
		$this->params []= $this->table;

		if (count($this->join)) {
			foreach ($this->join as $join) {
				$sql .= $join->getSql();
				$this->params = array_merge($this->params, $join->getParams());
			}
		}

		$params = array_combine($this->columns, $this->values);
		$values_parts = [];
		foreach ($params as $col => $val) {
			$values_parts []= '?i = ' . (isset( $this->placeholders[$col] ) ? $this->placeholders[$col] : '?');
			$this->params []= $col;
			$this->params []= $val;
		}
		$sql .= ' SET ' . join(', ', $values_parts);

		list($where, $params) = $this->getWhereSql();
		if (! empty($where)) {
			$sql .= ' WHERE ' . $where;
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

		return $sql;
	}
}
