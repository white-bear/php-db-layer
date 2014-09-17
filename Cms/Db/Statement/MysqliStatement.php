<?php

namespace Cms\Db\Statement;

use mysqli_result;
use Cms\Db\Statement\Result\MysqliResult;


/**
 * Реализация выражения для Mysql
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement
 */
class MysqliStatement extends BaseStatement
{
	const
		FETCH_ASSOC = MYSQLI_ASSOC,
		FETCH_ARRAY = MYSQLI_NUM;

	/**
	 * @var \mysqli_result
	 */
	protected $query_result = null;


	/**
	 * @param \mysqli_result $query_result
	 */
	public function __construct($query_result)
	{
		$this->query_result = $query_result;
		$this->result = new MysqliResult($query_result, static::FETCH_ASSOC);
	}

	/**
	 * Очищение результатов
	 */
	protected function freeResult()
	{
		if ($this->query_result !== null) {
			$this->query_result->free_result();
			$this->query_result = null;
		}

		$this->result = null;
	}
}
