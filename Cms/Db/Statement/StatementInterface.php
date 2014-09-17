<?php

namespace Cms\Db\Statement;


/**
 * Интерфейс реализации выражения
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement
 */
interface StatementInterface
{
	/**
	 * Выбор одной колонки одной записи
	 *
	 * @return string|null
	 */
	public function fetchOne();

	/**
	 * Выбор одной записи
	 *
	 * @return array|null
	 */
	public function fetchRow();

	/**
	 * Выбор одной колонки из всех записей
	 *
	 * @return array
	 */
	public function fetchCol();

	/**
	 * Выбор всех записей
	 *
	 * @return array
	 */
	public function fetchAll();

	/**
	 * Получение напрямую результата
	 *
	 * @return \Cms\Db\Statement\Result\ResultInterface
	 */
	public function fetchResult();

	/**
	 * Вызов своего обработчика на каждой записи
	 *
	 * @param  callable $callback
	 *
	 * @return int
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchResultCallback($callback);

	/**
	 * Выбор записей в виде ассоциативного массива
	 *
	 * @param  string $key
	 *
	 * @return array
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchAssoc($key);

	/**
	 * Выбор пар значений из записей
	 *
	 * @return array
	 *
	 * @throws \Cms\Db\Statement\StatementException
	 */
	public function fetchPairs();
}
