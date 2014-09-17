<?php

namespace Cms\Db\Statement\Result;

use \Iterator;


/**
 * Интерфейс для результата выполнения запроса
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement\Result
 */
interface ResultInterface extends Iterator
{
	/**
	 * Установка способа извлечения данных
	 *
	 * @param int $fetch_type
	 */
	public function setFetchType($fetch_type);

	/**
	 * Получение способа извлечения данных
	 *
	 * @return int
	 */
	public function getFetchType();

	/**
	 * Получение первой записи из результата
	 *
	 * @return mixed
	 */
	public function first();
}
