<?php

namespace Cms\Db\Sql;


/**
 * Реализация Sql delete
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
class Delete extends BaseSql
{
	protected $quick = false;


	/**
	 * Выполнение запроса без пересчета индексов
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function quick()
	{
		$this->quick = true;

		return $this;
	}

	/**
	 * Формирование результирующего SQL выражение
	 *
	 * @return string
	 */
	public function getSql()
	{
		$this->params = [];

		$sql = 'DELETE ' . $this->priority;
		if ($this->quick) {
			$sql .= ' QUICK';
		}

		if ($this->ignore) {
			$sql .= ' IGNORE';
		}

		$sql .= ' FROM ?i';
		$this->params []= $this->table;

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
