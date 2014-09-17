<?php

namespace Cms\Db\Engine\Driver;


/**
 * Интерфейс для драйвера БД
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine\Driver
 */
interface DriverInterface
{
	/**
	 * Установка соединения
	 *
	 * @return bool
	 *
	 * @throws \Cms\Db\Engine\Driver\Exception\ConnectionException
	 */
	public function connect();

	/**
	 * Закрытие коннекции
	 */
	public function close();

	/**
	 * Установлено ли соединение
	 *
	 * @return bool
	 */
	public function isConnected();

	/**
	 * Установка запроса, выполняемого при установлении соединения
	 *
	 * @param string $query
	 */
	public function addConnectQuery($query);

	/**
	 * Смена базы данных, к которой подключен драйвер
	 *
	 * @param  string $database
	 *
	 * @throws \Cms\Db\Engine\Driver\DriverException
	 */
	public function setDatabase($database);

	/**
	 * Получение выбранной базы данных
	 *
	 * @return string
	 */
	public function getDatabase();

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
	 * @param  string $query
	 * @param  bool   $use_result
	 * @param  bool   $async
	 * @param  bool   $no_retry
	 *
	 * @return \Cms\Db\Statement\StatementInterface|bool
	 *
	 * @throws \Cms\Db\Engine\Driver\Exception\ConnectionException
	 * @throws \Cms\Db\Engine\Driver\Exception\InvalidQueryException
	 */
	public function query($query, $use_result=false, $async=false, $no_retry=false);

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
	 * Получение всех возможных выражений драйвера
	 *
	 * @return array
	 */
	public function getAllStatements();

	/**
	 * Получение выражений чтения драйвера
	 *
	 * @return array
	 */
	public function getReadStatements();

	/**
	 * Получение выражений записи драйвера
	 *
	 * @return array
	 */
	public function getWriteStatements();

	/**
	 * Получение движка квотирования
	 *
	 * @return \Cms\Db\Query\Quotation\QuotationInterface
	 */
	public function getQuotation();
}
