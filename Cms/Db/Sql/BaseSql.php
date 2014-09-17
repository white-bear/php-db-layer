<?php

namespace Cms\Db\Sql;

use LogicException;


/**
 * Базовая реализация Sql выражений
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Sql
 */
abstract class BaseSql implements SqlInterface
{
	const
		PRIORITY_NORMAL  = '',
		PRIORITY_LOW     = 'LOW_PRIORITY',
		PRIORITY_HIGH    = 'HIGH_PRIORITY',
		PRIORITY_DELAYED = 'DELAYED';

	protected $table = '';
	protected $priority = self::PRIORITY_NORMAL;
	protected $ignore = false;
	protected $columns = [];
	protected $placeholders = [];
	protected $values = [];
	protected $order = [];
	protected $limit = [];
	protected $params = [];
	protected $where = [];
	protected $where_params = [];

	/** @var \Cms\Db\Sql\Join[] */
	protected $join = [];


	public function __construct($table='')
	{
		$this->into($table);
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
	 * Указание, в какую таблицу будет производиться вставка
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function into($table)
	{
		if (! empty($table)) {
			$this->table = $table;
		}

		return $this;
	}

	/**
	 * Указание, из какой таблицы будет происходить выборка данных
	 *
	 * @param  array|string $table   Имя таблицы, опционально указание алиаса
	 * @param  array        $columns Список выбираемых колонок
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function from($table, array $columns=[])
	{
		$this->into($table);

		foreach ($columns as $col) {
			if (is_string($col)) {
				$col = $table . '.' . $col;
			}

			$this->columns []= $col;
		}

		return $this;
	}

	/**
	 * Указание, какие колонки будут выбираться
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function columns(array $columns=[])
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Указание, какие колонки как будут квотироваться
	 *
	 * @param  array $placeholders
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function placeholders(array $placeholders)
	{
		$this->placeholders = $placeholders;

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
		$keys = array_keys($values);
		$firstKey = current($keys);

		if (is_string($firstKey)) {
			// Вайпаем данные если до этого передавались индексные данные
			if (count($this->columns) != count($this->values)) {
				$this->columns = $this->values = [];
			}

			foreach ($keys as $key) {
				$index = array_search($key, $this->columns);

				if ($index !== false) {
					$this->values[$index] = $values[$key];
				}
				else {
					$this->columns[] = $key;
					$this->values[] = $values[$key];
				}
			}
		}
		elseif (is_int($firstKey)) {
			$this->values = array_values($values);
		}

		return $this;
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
		throw new LogicException('Метод valuesList не реализован в строителе запроса');
	}

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

		return $this;
	}

	/**
	 * @param  \Cms\Db\Sql\SqlInterface|null $select
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function fromSelect(SqlInterface $select=null)
	{
		throw new LogicException('Метод fromSelect не реализован в строителе запроса');
	}

	/**
	 * Запрос с игнорированием ошибок
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function ignore()
	{
		$this->ignore = true;

		return $this;
	}

	/**
	 * Указание условий запроса
	 *
	 * @param  string $where
	 * @param  array  $params
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function where($where, array $params=[])
	{
		$this->where []= $where;
		$this->where_params = array_merge($this->where_params, $params);
		
		return $this;
	}

	/**
	 * @param  \Cms\Db\Sql\Join $join
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function join(Join $join)
	{
		$this->join []= $join;

		return $this;
	}

	/**
	 * Сортировка результатов
	 *
	 * @param  array $columns
	 * 
	 * @example ['id', '-name', 'type']  ===== ORDER BY id ASC, name DESC, type ASC
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function orderBy(array $columns=[])
	{
		$this->order = $columns;

		return $this;
	}

	/**
	 * Ограничение количества выбираемых результатов
	 *
	 * @param  int $limit
	 * @param  int $offset
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function limit($limit, $offset=0)
	{
		$this->limit = [$offset, $limit];

		return $this;
	}

	/**
	 * Выполнение запроса без пересчета индексов
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function quick()
	{
		throw new LogicException('Метод quick не реализован в строителе запроса');
	}

	/**
	 * Выполнение обновления записей в случае, если запись уже существует при вставке
	 *
	 * @param  array       $update_params
	 * @param  string|null $pk_name
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function updateOnDuplicate(array $update_params=[], $pk_name=null)
	{
		throw new LogicException('Метод updateOnDuplicate не реализован в строителе запроса');
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
		throw new LogicException('Метод quick не реализован в строителе запроса');
	}

	/**
	 * Режим выполнения запроса - обновление или вставка
	 *
	 * @param  string $mode
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function mode($mode='update')
	{
		throw new LogicException('Метод mode не реализован в строителе запроса');
	}

	/**
	 * Выбор только уникальных результатов
	 *
	 * @param  string $distinct
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function distinct($distinct='distinct')
	{
		throw new LogicException('Метод distinct не реализован в строителе запроса');
	}

	/**
	 * Выполнение запроса с отключенным кешем на уровне БД
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function noCache()
	{
		throw new LogicException('Метод noCache не реализован в строителе запроса');
	}

	/**
	 * Выбор количества строк, соответствующих указанным условиям
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function calcFoundRows()
	{
		throw new LogicException('Метод calcFoundRows не реализован в строителе запроса');
	}

	/**
	 * Группировка записей
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function groupBy(array $columns=[])
	{
		throw new LogicException('Метод groupBy не реализован в строителе запроса');
	}

	/**
	 * Условия для отсечения по аггрегациям
	 *
	 * @param  string $having
	 * @param  array  $params
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function having($having, array $params=[])
	{
		throw new LogicException('Метод having не реализован в строителе запроса');
	}

	/**
	 * Формирование Sql части для условий отсечения записей
	 *
	 * @return array
	 */
	protected function getWhereSql()
	{
		return [join(' AND ', $this->where), $this->where_params];
	}

	/**
	 * Формирование Sql части для ограничений
	 *
	 * @return array
	 */
	protected function getLimitSql()
	{
		if (empty($this->limit)) {
			return ['', []];
		}

		if ($this->limit[0] == 0) {
			return ["LIMIT ?d", [$this->limit[1]]];
		}

		return ["LIMIT ?d OFFSET ?d", [$this->limit[1], $this->limit[0]]];
	}

	/**
	 * Формирование Sql части для сортировки записей
	 *
	 * @return array
	 */
	protected function getOrderBySql()
	{
		if (empty($this->order)) {
			return ['', []];
		}

		$parts = [];
		$params = [];
		foreach ($this->order as $val) {
			$ord = 'ASC';
			// Если начинается с минуса, то сортировка DESC
			if ($val[0] == '-') {
				$ord = 'DESC';
				$val = substr($val, 1);
			}

			$parts []= '?i ' . $ord;
			$params []= $val;
		}

		return [
			'ORDER BY ' . join(', ', $parts),
			$params
		];
	}
}
