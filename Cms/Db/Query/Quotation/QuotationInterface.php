<?php

namespace Cms\Db\Query\Quotation;


/**
 * Интерфейс квотирования данных
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Quotation
 */
interface QuotationInterface
{
	const
		PARAM_PLAIN       = 'plain',
		PARAM_STR         = 'string',
		PARAM_STR_LIKE    = 'string_like',
		PARAM_STR_REGEXP  = 'string_regexp',
		PARAM_INT         = 'int',
		PARAM_FLOAT       = 'float',
		PARAM_SCALAR      = 'scalar',
		PARAM_COLUMN      = 'column',
		PARAM_REFERENCE   = 'reference',
		PARAM_LIST        = 'list',
		PARAM_PAIRS       = 'pairs',
		PARAM_INT_LIST    = 'int_list',
		PARAM_COLUMN_LIST = 'column_list',
		PARAM_EXPRESSION  = 'expression',
		PARAM_DATETIME    = 'datetime';


	/**
	 * Квотировать значение
	 *
	 * @param  string $value
	 * @param  string $quote_type
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	public function quote($value, $quote_type);
}
