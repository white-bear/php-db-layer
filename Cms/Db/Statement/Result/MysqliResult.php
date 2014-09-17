<?php

namespace Cms\Db\Statement\Result;

use mysqli_result;


/**
 * Реализация результата выполнения запроса для Mysql
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement\Result
 */
class MysqliResult extends BaseResult
{
	/**
	 * @var \mysqli_result
	 */
	protected $query_result = null;

	protected $fetch_type = MYSQLI_BOTH;


	/**
	 * @param \mysqli_result $query_result
	 * @param int|null       $fetch_type
	 */
	public function __construct($query_result, $fetch_type=null)
	{
		$this->query_result = $query_result;

		if ($fetch_type !== null) {
			$this->setFetchType($fetch_type);
		}
	}

	/**
	 * Установка способа извлечения данных
	 *
	 * @param int $fetch_type
	 */
	public function setFetchType($fetch_type)
	{
		$this->fetch_type = $fetch_type;
	}

	/**
	 * Получение способа извлечения данных
	 *
	 * @return int
	 */
	public function getFetchType()
	{
		return $this->fetch_type;
	}

	/**
	 * Извлечение данных
	 *
	 * @return array|null
	 */
	protected function fetch()
	{
		if (! $this->isValidResult()) {
			return null;
		}

		return $this->query_result->fetch_array($this->fetch_type);
	}

	/**
	 * Проверка, валиден ли результат
	 *
	 * @return bool
	 */
	protected function isValidResult()
	{
		return $this->query_result instanceof mysqli_result;
	}
}
