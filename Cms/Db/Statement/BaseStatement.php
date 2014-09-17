<?php

namespace Cms\Db\Statement;


/**
 * Базовая реализация выражения
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement
 */
abstract class BaseStatement implements StatementInterface
{
	const EMPTY_ROW_RETURN = null;

	const
		FETCH_ASSOC = 1,
		FETCH_ARRAY = 2;

	/**
	 * @var \Cms\Db\Statement\Result\ResultInterface
	 */
	protected $result = null;


	/**
	 * Выбор одной колонки одной записи
	 *
	 * @return string|null
	 */
	public function fetchOne()
	{
		$this->result->setFetchType(static::FETCH_ARRAY);

		$data = $this->result->first();
		if ($data === null) {
			return static::EMPTY_ROW_RETURN;
		}

		return $data[0];
	}

	/**
	 * Выбор одной записи
	 *
	 * @return array|null
	 */
	public function fetchRow()
	{
		$this->result->setFetchType(static::FETCH_ASSOC);

		$data = $this->result->first();
		if ($data === null) {
			return self::EMPTY_ROW_RETURN;
		}

		return $data;
	}

	/**
	 * Выбор одной колонки из всех записей
	 *
	 * @return array
	 */
	public function fetchCol()
	{
		$this->result->setFetchType(static::FETCH_ARRAY);

		$result = [];
		foreach ($this->result as $data) {
			$result []= $data[0];
		}

		return $result;
	}

	/**
	 * Выбор всех записей
	 *
	 * @return array
	 */
	public function fetchAll()
	{
		$this->result->setFetchType(static::FETCH_ASSOC);

		$result = [];
		foreach ($this->result as $data) {
			$result []= $data;
		}

		return $result;
	}

	/**
	 * Получение напрямую результата
	 *
	 * @return \Cms\Db\Statement\Result\ResultInterface
	 */
	public function fetchResult()
	{
		return $this->result;
	}

	/**
	 * Вызов своего обработчика на каждой записи
	 *
	 * @param  callable $callback
	 *
	 * @return int
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchResultCallback($callback)
	{
		if (! is_callable($callback)) {
			$this->freeResult();

			throw new StatementException("Ожидалась функция-обработчик, на входе: " . gettype($callback));
		}

		$this->result->setFetchType(static::FETCH_ASSOC);

		$iter = 0;
		foreach ($this->result as $data) {
			call_user_func_array($callback, [$data, $iter]);
			$iter++;
		}

		return $iter;
	}

	/**
	 * Выбор записей в виде ассоциативного массива
	 *
	 * @param  string $key
	 *
	 * @return array
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchAssoc($key)
	{
		if (! is_string($key)) {
			$this->freeResult();

			throw new StatementException("Некорректный тип - ожидался string, на входе: " . gettype($key));
		}

		if (empty($key)) {
			$this->freeResult();

			throw new StatementException("Ключ не может быть пустым");
		}

		$this->result->setFetchType(static::FETCH_ASSOC);

		$result = [];
		foreach ($this->result as $data) {
			if (! array_key_exists($key, $data)) {
				$this->freeResult();
				$msg = "В выборке не содержится указанного ключа '{$key}'";

				throw new StatementException($msg);
			}

			$result[ $data[$key] ] = $data;
		}

		return $result;
	}

	/**
	 * Выбор пар значений из записей
	 *
	 * @return array
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchPairs()
	{
		$this->result->setFetchType(static::FETCH_ARRAY);

		$result = [];
		foreach ($this->result as $data) {
			if (count($data) != 2) {
				$this->freeResult();
				$msg = "При выборе пар необходимо, чтобы результат выборки содержал строго 2 поля, имеем: " . count($data);

				throw new StatementException($msg);
			}

			$result[ $data[0] ] = $data[1];
		}

		return $result;
	}

	/**
	 * Очищение результатов при удалении объекта
	 */
	public function __destruct()
	{
		$this->freeResult();
	}

	/**
	 * Очищение результатов
	 */
	abstract protected function freeResult();
}
