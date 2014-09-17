<?php

namespace Cms\Db;

use Cms\Db\Engine\EngineInterface;
use Cms\Db\Engine\Driver\DriverRWAccessor;
use Cms\Db\Query\QueryBuilder;
use Cms\Db\Meta\MetaAccessor;
use Cms\Db\Sql\SqlInterface;


/**
 * Слой базы данных
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db
 */
class Engine implements EngineInterface
{
	use DriverRWAccessor;
	use MetaAccessor;

	protected $connect_queries = [];

	/**
	 * @var \Cms\Db\Query\QueryBuilder
	 */
	protected $query_builder = null;

	/**
	 * Выполняется транзакция
	 *
	 * @var bool
	 */
	protected $in_transaction = false;

	/**
	 * Принудительное выполнение запроса на мастере
	 *
	 * @var bool
	 */
	protected $force_master = false;


	/**
	 * Старт транзакции
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		$this->in_transaction = true;

		return $this->driver_write->beginTransaction();
	}

	/**
	 * Коммит транзакции
	 *
	 * @return bool
	 */
	public function commit()
	{
		$this->in_transaction = false;

		return $this->driver_write->commit();
	}

	/**
	 * Откат транзакции
	 *
	 * @return bool
	 */
	public function rollback()
	{
		$this->in_transaction = false;

		return $this->driver_write->rollback();
	}

	/**
	 * Закрытие коннекций
	 */
	public function close()
	{
		$this->driver_write->close();
		$this->driver->close();
	}

	/**
	 * Принудительное выполнение запроса на мастере
	 *
	 * @return \Cms\Db\Engine
	 */
	public function master()
	{
		$this->force_master = true;

		return $this;
	}

	/**
	 * Выполнение запроса
	 *
	 * @param  string      $query
	 * @param  array       $params
	 * @param  bool        $use_result
	 * @param  bool        $async       Выполнение запроса асинхронно, без ожидания окончания его выполнение
	 * @param  string|null $driver_type Указание, через какой драйвер выполнять запрос
	 *
	 * @return \Cms\Db\Statement\StatementInterface|bool
	 *
	 * @throws \Cms\Db\Engine\Driver\DriverException
	 */
	public function query($query, array $params=[], $use_result=false, $async=false, $driver_type=null)
	{
		$qb = $this->getQueryBuilder();

		if ($query instanceof SqlInterface) {
			$sql = $query->getSql();
			$params = $query->getParams();
			$query = $sql;
		}

		$qb->setParams($params);

		$quoted_query = $qb->assemble($query);

		$driver = $this->getDriverForQuery($query, $driver_type);

		return $driver->query($quoted_query, $use_result, $async);
	}

	/**
	 * Возвращение идентификатора вставленной записи
	 *
	 * @return int
	 */
	public function getLastInsertId()
	{
		return $this->driver_write->getLastInsertId();
	}

	/**
	 * Получение количества обработанных записей
	 *
	 * @return int
	 */
	public function getAffectedRows()
	{
		return $this->driver_write->getAffectedRows();
	}

	/**
	 * Получение строителя запросов
	 *
	 * @return \Cms\Db\Query\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		if ($this->query_builder === null) {
			$this->query_builder = new QueryBuilder();

			$this->query_builder->setQuotation($this->driver->getQuotation());
		}

		return $this->query_builder;
	}

	/**
	 * Установка строителя запросов
	 *
	 * @param \Cms\Db\Query\QueryBuilder $query_builder
	 */
	public function setQueryBuilder($query_builder)
	{
		$this->query_builder = $query_builder;
	}

	/**
	 * Получение драйвера для запроса
	 *
	 * @param  string      $query
	 * @param  string|null $type
	 *
	 * @return \Cms\Db\Engine\Driver\DriverInterface
	 */
	protected function getDriverForQuery($query, $type=null)
	{
		if ($this->driver === $this->driver_write) {
			return $this->driver_write;
		}

		if ($this->in_transaction) {
			return $this->driver_write;
		}

		if ($this->force_master) {
			$this->force_master = false;

			return $this->driver_write;
		}

		if (is_string($type) && in_array($type, ['read', 'write'])) {
			if ($type == 'write') {
				return $this->driver_write;
			}

			return $this->driver;
		}

		$driver_name = 'driver';
		$statements = $this->driver_write->getWriteStatements();
		$pattern = sprintf('~^\s*(%s)\s~usi', join('|', $statements));

		if (preg_match($pattern, $query, $matches)) {
			$driver_name = 'driver_write';
		}

		return $this->$driver_name;
	}
}
