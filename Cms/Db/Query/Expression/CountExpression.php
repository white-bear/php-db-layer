<?php

namespace Cms\Db\Query\Expression;


/**
 * sql выражение Count
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
class CountExpression extends BaseExpression
{
	protected $value = '';


	/**
	 * @param string $value Поле, по которому будет происходить аггрегация
	 */
	public function __construct($value='*')
	{
		$this->value = $value;
	}

	/**
	 * Проверка корректности выражения
	 *
	 * @return bool
	 */
	public function validate()
	{
		if (! is_string($this->value)) {
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

		$query = 'COUNT(?i)';
		$placeholder_engine->setParams([$this->value]);

		if (! empty($this->alias)) {
			$query .= ' AS ?i';
			$placeholder_engine->setParams([$this->value, $this->alias]);
		}

		return $placeholder_engine->bindParams($query);
	}
}
