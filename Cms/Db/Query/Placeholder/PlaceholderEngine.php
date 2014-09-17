<?php

namespace Cms\Db\Query\Placeholder;

use Cms\Db\Query\Quotation\QuotationAccessor,
	Cms\Db\Query\Quotation\QuotationInterface;


/**
 * Движок подстановки значений в запросы
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Placeholder
 */
class PlaceholderEngine
{
	use QuotationAccessor;

	private $params = [];

	/**
	 * Список зарегистрированных модификаторов
	 *
	 * @var array
	 */
	static private $params_marks = [
		'e' => QuotationInterface::PARAM_STR_LIKE,
		'E' => QuotationInterface::PARAM_STR_REGEXP,
		'l' => QuotationInterface::PARAM_LIST,
		'a' => QuotationInterface::PARAM_PAIRS,
		'i' => QuotationInterface::PARAM_COLUMN,
		'r' => QuotationInterface::PARAM_COLUMN_LIST,
		'd' => QuotationInterface::PARAM_INT,
		'f' => QuotationInterface::PARAM_FLOAT,
		's' => QuotationInterface::PARAM_PLAIN,
		'S' => QuotationInterface::PARAM_STR,
		'n' => QuotationInterface::PARAM_REFERENCE,
		'I' => QuotationInterface::PARAM_INT_LIST,
		'x' => QuotationInterface::PARAM_EXPRESSION,
		''  => QuotationInterface::PARAM_SCALAR,
	];


	/**
	 * Получение параметров запроса
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Установка параметров запроса
	 *
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Привязка значений к запросу
	 *
	 * @param  string $query
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Placeholder\PlaceholderException
	 */
	public function bindParams($query)
	{
		if (empty($query)) {
			throw new PlaceholderException('Использовать пустые запросы запрещено');
		}

		$pattern = sprintf('~\?(%s)(?![a-z0-9])~',
			join('|', array_keys(static::$params_marks))
		);

		$params = $this->getParams();
		preg_match_all($pattern, $query, $matches, PREG_OFFSET_CAPTURE);
		foreach (array_reverse($matches[1]) as $match) {
			list($mark, $pos) = $match;
			$query = substr($query, 0, $pos - 1) . $this->quoteParam($mark, $params) . substr($query, $pos + strlen($mark));
		}

		if (! empty($params)) {
			throw new PlaceholderException('Количество аргументов больше, чем плейсхолдеров в запросе');
		}

		return $query;
	}

	/**
	 * Привязка значения к запросу
	 *
	 * @param  string $mark
	 * @param  array  &$params
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Placeholder\PlaceholderException
	 */
	private function quoteParam($mark, array &$params)
	{
		if (empty($params)) {
			throw new PlaceholderException('Количество плейсхолдеров больше, чем полученных данных');
		}

		if (! isset( static::$params_marks[$mark] )) {
			throw new PlaceholderException("Неизвестная метка \"{$mark}\"");
		}

		$quote_type = static::$params_marks[$mark];
		$value = array_pop($params);

		$quotation = $this->getQuotation();

		return $quotation->quote($value, $quote_type);
	}
}
