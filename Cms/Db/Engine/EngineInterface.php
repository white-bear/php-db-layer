<?php

namespace Cms\Db\Engine;


/**
 * Интерфейс движка БД
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine
 */
interface EngineInterface
{
	/**
	 * Старт транзакции
	 *
	 * @return bool
	 */
	public function beginTransaction();

	/**
	 * Коммит транзакции
	 *
	 * @return bool
	 */
	public function commit();

	/**
	 * Откат транзакции
	 *
	 * @return bool
	 */
	public function rollback();

	/**
	 * Выполнение запроса
	 *
	 * @param string      $query
	 * @param array       $params
	 * @param string|null $driver_type
	 * @param bool        $async
	 *
	 * @return \Cms\Db\Statement\StatementInterface|bool
	 *
	 * @throws \Cms\Db\Engine\Driver\DriverException
	 */
	public function query($query, array $params=[], $driver_type=null, $async=false);

	/**
	 * Возвращение идентификатора вставленной записи
	 *
	 * @return int
	 */
	public function getLastInsertId();

	/**
	 * Получение количества обработанных записей
	 *
	 * @return int
	 */
	public function getAffectedRows();

	/**
	 * @param \Cms\Db\Engine\Driver\DriverInterface $driver
	 */
	public function setDriver($driver);

	/**
	 * @return \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	public function getDriver();

	/**
	 * @param string $query
	 */
	public function addConnectQuery($query);

	/**
	 * @param \Cms\Db\Engine\Driver\DriverInterface $driver
	 */
	public function setWriteDriver($driver);

	/**
	 * @return \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	public function getWriteDriver();
}
