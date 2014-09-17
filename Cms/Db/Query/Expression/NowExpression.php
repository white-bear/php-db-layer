<?php

namespace Cms\Db\Query\Expression;


/**
 * sql выражение Now
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
class NowExpression extends BaseExpression
{
	protected $interval = '';


	/**
	 * @param string $interval Интервал, который отсчитывается от текущей даты
	 */
	public function __construct($interval='')
	{
		$this->interval = $interval;
	}

	/**
	 * Проверка корректности выражения
	 *
	 * @return bool
	 */
	public function validate()
	{
		if (! is_string($this->interval)) {
			return false;
		}

		if (preg_match('~^([\+\-]\s+|)(\d+[\d\.\:\-]*)\s+([a-z_]+)$~usi', trim($this->interval), $matches)) {
			$sign = '+';
			if (! empty($matches[1])) {
				$sign = trim($matches[1]);
			}

			$this->interval = sprintf('%s INTERVAL %s %s', $sign, $matches[2], $matches[3]);

			return true;
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
		return 'NOW() ' . $this->interval;
	}
}
