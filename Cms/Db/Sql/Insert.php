<?php

namespace Cms\Db\Sql;

use LogicException;


/**
 * Реализация Sql insert
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
class Insert extends BaseSql
{
	protected $values_list = false;
	protected $update = false;
	protected $update_pk_name = null;
	protected $update_params = [];
	/** @var \Cms\Db\Sql\Select|null */
	protected $select = null;


	/**
	 * Изменение приоритета запроса
	 *
	 * @param  string $priority
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function priority($priority='low')
	{
		if ($priority == 'low') {
			$this->priority = self::PRIORITY_LOW;
		}
		elseif ($priority == 'high') {
			$this->priority = self::PRIORITY_HIGH;
		}
		elseif ($priority == 'delayed') {
			$this->priority = self::PRIORITY_DELAYED;
		}

		return $this;
	}

	/**
	 * @param  \Cms\Db\Sql\SqlInterface|null $select
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function fromSelect(SqlInterface $select=null)
	{
		$this->select = $select;

		return $this;
	}

	/**
	 * Выполнение обновления записей в случае, если запись уже существует при вставке
	 *
	 * @param  array       $update_params
	 * @param  string|null $pk_name
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function updateOnDuplicate(array $update_params=[], $pk_name=null)
	{
		$this->update = true;
		$this->update_params = $update_params;
		$this->update_pk_name = $pk_name;

		return $this;
	}

	/**
	 * Добавление вставляемых значений
	 *
	 * @param  array $values
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function values(array $values=[])
	{
		$this->values_list = false;

		return parent::values($values);
	}

	/**
	 * Добавление значений для массовой вставки
	 *
	 * @param  array $values
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function valuesList(array $values=[])
	{
		$this->values_list = true;

		if (count($values) == 0) {
			return $this;
		}

		$first_list = reset($values);
		if (! is_array($first_list)) {
			throw new LogicException('Метод valuesList ожидает на вход массив значений, пришел массив ' . gettype($first_list));
		}

		$keys = array_keys($first_list);
		$firstKey = current($keys);

		if (is_string($firstKey)) {
			// Вайпаем данные если до этого передавались индексные данные
			if (count($this->columns) != count($keys)) {
				$this->columns = $this->values = [];
			}

			foreach ($values as $values_entry) {
				$row = [];

				foreach ($keys as $key) {
					$index = array_search($key, $this->columns);

					if ($index !== false) {
						$row[$index] = $values_entry[$key];
					}
					else {
						$this->columns[] = $key;
						$row[] = $values_entry[$key];
					}
				}

				$this->values[] = $row;
			}
		}
		elseif (is_int($firstKey)) {
			foreach ($values as $values_entry) {
				$this->values []= array_values($values_entry);
			}
		}

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

		$sql = 'INSERT ' . $this->priority;
		if ($this->ignore) {
			$sql .= ' IGNORE';
		}

		$sql .= ' INTO ?i (?r)';
		$this->params []= $this->table;
		$this->params []= $this->columns;

		if ($this->select !== null) {
			$sql .= ' ' . $this->select->getSql();
			$this->params = array_merge($this->params, $this->select->getParams());
		}
		elseif (! $this->values_list) {
			$values_parts = [];
			foreach ($this->columns as $i => $col) {
				$val = $this->values[$i];
				$values_parts []= isset( $this->placeholders[$col] ) && ! is_object($val) ? $this->placeholders[$col] : '?';
				$this->params []= $val;
			}
			$sql .= ' VALUES (' . join(', ', $values_parts) . ')';
		}
		else {
			$sql .= ' VALUES ';
			foreach ($this->values as $values_list) {
				$values_parts = [];
				foreach ($this->columns as $i => $col) {
					$val = $values_list[$i];
					$values_parts []= isset( $this->placeholders[$col] ) && ! is_object($val) ? $this->placeholders[$col] : '?';
					$this->params []= $val;
				}
				$sql .= '(' . join(', ', $values_parts) . '), ';
			}

			$sql = substr($sql, 0, -2);
		}

		if ($this->update) {
			$params = $this->update_params;
			if (empty($this->update_params)) {
				$params = array_combine($this->columns, $this->values);
			}

			$values_parts = [];
			foreach ($params as $col => $val) {
				$values_parts []= '?i = ' . (isset( $this->placeholders[$col] ) && ! is_object($val) ? $this->placeholders[$col] : '?');
				$this->params []= $col;
				$this->params []= $val;
			}
			$sql .= ' ON DUPLICATE KEY UPDATE ' . join(', ', $values_parts);

			if ($this->update_pk_name !== null) {
				$sql .= ', ?i = LAST_INSERT_ID(?i)';
				$this->params []= $this->update_pk_name;
				$this->params []= $this->update_pk_name;
			}
		}

		return $sql;
	}
}
