<?php

namespace Cms\Db\Sql;


/**
 * Реализация Sql replace
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
class Replace extends BaseSql
{
	static protected $modes = [
		self::MODE_UPDATE,
		self::MODE_INSERT,
	];

	protected $mode = self::MODE_INSERT;


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
		elseif ($priority == 'delayed') {
			$this->priority = self::PRIORITY_DELAYED;
		}

		return $this;
	}

	/**
	 * Режим выполнения запроса - обновление или вставка
	 *
	 * @param  string $mode
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function mode($mode='update')
	{
		if (in_array($mode, static::$modes)) {
			$this->mode = $mode;
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

		$sql = 'REPLACE ' . $this->priority;

		$sql .= ' INTO ?i';
		$this->params []= $this->table;

		if ($this->mode == self::MODE_INSERT) {
			$sql .= ' (?r)';
			$this->params []= $this->columns;

			$values_parts = [];
			foreach ($this->columns as $i => $col) {
				$values_parts []= isset( $this->placeholders[$col] ) ? $this->placeholders[$col] : '?';
				$this->params []= $this->values[$i];
			}
			$sql .= ' VALUES (' . join(', ', $values_parts) . ')';
		}
		else {
			$params = array_combine($this->columns, $this->values);
			$values_parts = [];
			foreach ($params as $col => $val) {
				$values_parts []= '?i = ' . (isset( $this->placeholders[$col] ) ? $this->placeholders[$col] : '?');
				$this->params []= $col;
				$this->params []= $val;
			}
			$sql .= ' SET ' . join(', ', $values_parts);
		}

		return $sql;
	}
}
