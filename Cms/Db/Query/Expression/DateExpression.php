<?php

namespace Cms\Db\Query\Expression;

use DateTime;


/**
 * sql выражение Date
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
class DateExpression extends BaseExpression
{
	const DATE_FORMAT = 'Y-m-d';

	protected $value = '';


	/**
	 * @param $value Дата
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
		if (is_numeric($this->value)) {
			$this->value = date(self::DATE_FORMAT, intval($this->value));

			return true;
		}

		$dt = DateTime::createFromFormat(self::DATE_FORMAT, $this->value);
		if ($dt === false) {
			return false;
		}

		$this->value = $dt->format(self::DATE_FORMAT);

		return true;
	}

	/**
	 * Приведение выражения к строковому представлению
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf("DATE('%s')", $this->value);
	}
}
