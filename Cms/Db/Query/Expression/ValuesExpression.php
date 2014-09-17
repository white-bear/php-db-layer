<?php

namespace Cms\Db\Query\Expression;


/**
 * Class ValuesExpression
 * @package Cms\Db\Query\Expression
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class ValuesExpression extends BaseExpression
{
	protected $value = '';


	/**
	 * @param string $value Поле, которое будет извлекаться
	 */
	public function __construct($value)
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

		$query = 'VALUES(?i)';
		$placeholder_engine->setParams([$this->value]);

		return $placeholder_engine->bindParams($query);
	}
}
