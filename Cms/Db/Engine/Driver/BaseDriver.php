<?php

namespace Cms\Db\Engine\Driver;


/**
 * Базовая реализация драйвера
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine\Driver
 */
abstract class BaseDriver implements DriverInterface
{
	static protected $read_statements = [
		'select',
		'explain',
		'show',
		'describe',
		'handler',
		'set',
		'prepare',
		'execute',
		'do',
	];

	static protected $write_statements = [
		'insert',
		'update',
		'delete',
		'replace',
		'grant',
		'revoke',
		'rename',
		'truncate',
		'drop',
		'flush',
		'load',
		'create',
		'call',
		'cache',
		'reset',
		'kill',
	];

	protected $resource = null;

	protected $options = [
		'persistent',
		'database',
		'host',
		'port',
		'user',
		'password',
		'timeout',
	];

	protected $persistent = false;

	protected $database = '';

	protected $host = '127.0.0.1';

	protected $port = null;

	protected $user = 'root';

	protected $password = '';

	protected $connected = false;

	protected $connect_queries = [];

	protected $timeout = 2;


	/**
	 * Установлено ли соединение
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->connected;
	}

	/**
	 * Установка запроса, выполняемого при установлении соединения
	 *
	 * @param string $query
	 */
	public function addConnectQuery($query)
	{
		$this->connect_queries []= $query;
	}

	/**
	 * Получение выбранной базы данных
	 *
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Постоянное ли соединение
	 *
	 * @return bool
	 */
	public function isPersistent()
	{
		return $this->persistent;
	}

	/**
	 * Получение всех возможных выражений драйвера
	 *
	 * @return array
	 */
	public function getAllStatements()
	{
		return array_merge(static::$read_statements, static::$write_statements);
	}

	/**
	 * Получение выражений чтения драйвера
	 *
	 * @return array
	 */
	public function getReadStatements()
	{
		return static::$read_statements;
	}

	/**
	 * Получение выражений записи драйвера
	 *
	 * @return array
	 */
	public function getWriteStatements()
	{
		return static::$write_statements;
	}

	/**
	 * Выполнение запросов при соединении
	 *
	 * @return bool
	 */
	protected function executeConnectQueries()
	{
		if (! $this->isConnected()) {
			return false;
		}

		foreach ($this->connect_queries as $query) {
			$this->query($query);
		}

		return true;
	}
}
