<?php

namespace Cms\Db\Query\Expression;


/**
 * Class SumExpression
 * @package Cms\Db\Query\Expression
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class SumExpression extends BaseExpression
{
	protected $column = '';


	/**
	 * @param string $column Поле, по которому будет происходить аггрегация
	 */
	public function __construct($column)
	{
		$this->column = $column;
	}

	/**
	 * Проверка корректности выражения
	 *
	 * @return bool
	 */
	public function validate()
	{
		if (! is_string($this->column)) {
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

		$query = 'SUM(?i)';
		$placeholder_engine->setParams([$this->column]);

		if (! empty($this->alias)) {
			$query .= ' AS ?i';
			$placeholder_engine->setParams([$this->column, $this->alias]);
		}

		return $placeholder_engine->bindParams($query);
	}
}
