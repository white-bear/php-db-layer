<?php

namespace Cms\Db\Query\Expression;


/**
 * пользовательское sql выражение
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
class QueryExpression extends BaseExpression
{
	protected $query = '';
	protected $params = [];


	/**
	 * @param string $query  Запрос
	 * @param array  $params Параметры запроса
	 */
	public function __construct($query, array $params=[])
	{
		$this->query = $query;
		$this->params = $params;
	}

	/**
	 * Проверка корректности выражения
	 *
	 * @return bool
	 */
	public function validate()
	{
		if (empty($this->query)) {
			return false;
		}

		return true;
	}

	/**
	 * Приведение выражения к строковому представлению
	 *
	 * @return string
	 */
	public function __toString()
	{
		$placeholder_engine = $this->getPlaceholderEngine();

		$placeholder_engine->setParams($this->params);

		return $placeholder_engine->bindParams($this->query);
	}
}
