<?php

namespace Cms\Db\Query\Quotation;

use Cms\Db\Query\Expression\ExpressionInterface;


/**
 * Базовая реализация квотирования данных
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Quotation
 */
abstract class BaseQuotation implements QuotationInterface
{
	/**
	 * Проверка, является ли строка бинарной
	 *
	 * @param  string $value
	 *
	 * @return bool
	 */
	protected function isBinary($value)
	{
		return (bool) preg_match('~[\x00-\x1f\x7f](?<![\t\n\r])~sSX', $value);
	}

	/**
	 * Проверка, является ли значение выражением
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected function isExpression($value)
	{
		return $value instanceof ExpressionInterface;
	}
}
