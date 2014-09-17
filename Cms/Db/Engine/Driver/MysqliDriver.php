<?php

namespace Cms\Db\Engine\Driver;

use mysqli, mysqli_result;
use Cms\Db\Query\Quotation\MysqliQuotation;
use Cms\Db\Statement\MysqliStatement;
use
	Cms\Db\Engine\Driver\Exception\ConnectionException,
	Cms\Db\Engine\Driver\Exception\ProcessQueryException,
	Cms\Db\Engine\Driver\Exception\InvalidQueryException;


/**
 * Драйвер БД Mysql
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine\Driver
 */
class MysqliDriver extends BaseDriver
{
	const
		ERROR_DEADLOCK = 1213,
		ERROR_DUPLICATE_ENTRY  = 1062, // ERROR 1062 (23000): Duplicate entry '...' for key '...'
		ERROR_SERVER_GONE_AWAY = 2006; // ERROR 2006 (HY000): MySQL server has gone away

	/**
	 * @var \mysqli
	 */
	protected $resource = null;


	/**
	 * @param array $options
	 */
	public function __construct(array $options=[])
	{
		foreach ($this->options as $opt) {
			if (! empty($options[$opt])) {
				$this->$opt = $options[$opt];
			}
		}
	}

	/**
	 * Закрытие коннекции при удалении класса
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Получение ресурса драйвера
	 *
	 * @return \mysqli
	 */
	protected function getResource()
	{
		if ($this->isConnected()) {
			return $this->resource;
		}

		$this->connect();

		return $this->resource;
	}

	/**
	 * Установка соединения
	 *
	 * @return bool
	 *
	 * @throws \Cms\Db\Engine\Driver\Exception\ConnectionException
	 */
	public function connect()
	{
		$resource = null;
		$this->resource = null;
		$this->connected = false;

		$host = $this->host;
		if ($this->isPersistent()) {
			$host = 'p:' . $host;
		}

		try {
			$resource = mysqli_init();
			$resource->set_opt(MYSQLI_OPT_CONNECT_TIMEOUT, $this->timeout);
			$resource->real_connect(
				$host,
				$this->user,
				$this->password,
				$this->database,
				intval($this->port)
			);
		}
		catch (\Exception $e) {
			$msg = sprintf('Не удалось установить соединение с базой данных "%s": %s',
				$this->database,
				$e->getMessage()
			);

			throw new ConnectionException($msg, $e->getCode());
		}

		if ($resource->connect_errno) {
			$msg = sprintf('Не удалось установить соединение с базой данных "%s": %s',
				$this->database,
				$resource->connect_error
			);

			throw new ConnectionException($msg, $resource->connect_errno);
		}

		$this->resource = $resource;
		$this->connected = true;

		$this->executeConnectQueries();

		return true;
	}

	/**
	 * Закрытие коннекции
	 */
	public function close()
	{
		if (! $this->isConnected() || $this->isPersistent()) {
			return;
		}

		$this->resource->close();
		$this->resource = null;
		$this->connected = false;
	}

	/**
	 * Смена базы данных, к которой подключен драйвер
	 *
	 * @param  string $database
	 *
	 * @throws \Cms\Db\Engine\Driver\DriverException
	 */
	public function setDatabase($database)
	{
		if ($this->isConnected()) {
			if (! $this->getResource()->select_db($database)) {
				throw new DriverException("Не удалось сменить базу данных с '{$this->database}' на '{$database}'.");
			}
		}

		$this->database = $database;
	}

	/**
	 * Старт транзакции
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->getResource()->autocommit(false);
	}

	/**
	 * Коммит транзакции
	 *
	 * @return bool
	 */
	public function commit()
	{
		$resource = $this->getResource();

		$result = $resource->commit();
		$resource->autocommit(true);

		return $result;
	}

	/**
	 * Откат транзакции
	 *
	 * @return bool
	 */
	public function rollback()
	{
		return $this->getResource()->rollback();
	}

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
	 * @throws \Cms\Db\Engine\Driver\Exception\ProcessQueryException
	 * @throws \Cms\Db\Engine\Driver\Exception\InvalidQueryException
	 */
	public function query($query, $use_result=false, $async=false, $no_retry=false)
	{
		$resource = $this->getResource();

		$resultmode = MYSQLI_STORE_RESULT;

		if ($use_result) {
			$resultmode = MYSQLI_USE_RESULT;
		}

		if ($async) {
			$resultmode = MYSQLI_ASYNC;
		}

		$result = $resource->query($query, $resultmode);

		/* попытка повторного выполнения запроса в случае деадлока */
		if ($resource->errno == self::ERROR_DEADLOCK) {
			$result = $resource->query($query, $resultmode);
		}
		elseif ($resource->errno == self::ERROR_SERVER_GONE_AWAY) {
			$this->close();
			$this->connect();
			$result = $resource->query($query, $resultmode);
		}

		if ($resource->errno != 0) {
			$msg = sprintf('%s in %s',
				$resource->error,
				str_replace("\n", ' ', $query)
			);

			throw new InvalidQueryException($msg, $resource->errno);
		}

		if ($resource->warning_count > 0) {
			$warning = $resource->get_warnings();
			$msg = sprintf('%s in %s',
				$warning->message,
				str_replace("\n", ' ', $query)
			);

			throw new ProcessQueryException($msg, $warning->errno);
		}

		if ($result instanceof mysqli_result) {
			return new MysqliStatement($result);
		}

		return true;
	}

	/**
	 * Возвращение идентификатора вставленной записи
	 *
	 * @return int
	 */
	public function getLastInsertId()
	{
		return $this->getResource()->insert_id;
	}

	/**
	 * Получение количества обработанных записей
	 *
	 * @return int
	 */
	public function getAffectedRows()
	{
		return $this->getResource()->affected_rows;
	}

	/**
	 * Получение движка квотирования
	 *
	 * @return \Cms\Db\Query\Quotation\QuotationInterface
	 */
	public function getQuotation()
	{
		return new MysqliQuotation();
	}
}
