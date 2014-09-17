<?php

namespace Cms\Db\Query\Quotation;

use Cms\Utils\Text;
use Cms\Utils\Dict;


/**
 * Реализация квотирования данных для Mysql
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Quotation
 */
class MysqliQuotation extends BaseQuotation
{
	/**
	 * Каким символом квотировать идентификаторы
	 */
	const QUOTE_IDENTIFIER = '`';

	/**
	 * Квотрировать значение
	 *
	 * @param  mixed  $value
	 * @param  string $quote_type
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	public function quote($value, $quote_type)
	{
		if ($this->isExpression($value)) {
			return $this->quoteExpression($value);
		}

		$quotation = lcfirst(Text::fromString('quote_' . $quote_type)->camelCase());
		if (! method_exists($this, $quotation)) {
			$msg = sprintf('Квотирование для %s не реализовано', $quote_type);

			throw new QuotationException($msg);
		}

		return $this->$quotation($value);
	}

	/**
	 * Значение без квотирования
	 *
	 * @param  string $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quotePlain($value)
	{
		if (! is_string($value)) {
			throw new QuotationException("Некорректный тип - ожидался string, на входе: " . gettype($value));
		}

		return strval($value);
	}

	/**
	 * Приведение даты к строковому виду
	 *
	 * @param  \DateTime $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteDatetime($value)
	{
		if (! $value instanceof \DateTime) {
			throw new QuotationException("Некорректный тип - ожидался DateTime, на входе: " . gettype($value));
		}

		return "'" . $value->format('Y-m-d H:i:s') . "'";
	}

	/**
	 * Приведение выражения к строковому виду
	 *
	 * @param  \Cms\Db\Query\Expression\ExpressionInterface $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteExpression($value)
	{
		if (! $this->isExpression($value)) {
			throw new QuotationException("Некорректный тип - ожидался expression, на входе: " . gettype($value));
		}

		if (! $value->validate()) {
			throw new QuotationException("Некорректные данные в выражении " . get_class($value));
		}

		return strval($value);
	}

	/**
	 * Квотирование строки в двоичном виде
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	protected function quoteBinaryString($value)
	{
		if ($value === null) {
			return 'NULL';
		}

		return '0x' . bin2hex($value);
	}

	/**
	 * Квотирование скалярных значений
	 *
	 * @param  string|int|float $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteString($value)
	{
		if ($value === null) {
			return 'NULL';
		}

		if (! is_scalar($value)) {
			throw new QuotationException("Некорректный тип - ожидался scalar, на входе: " . gettype($value));
		}

		if ($this->isBinary($value)) {
			return $this->quoteBinaryString($value);
		}

		$value = strtr(
			$value,
			[
				'\\' => '\\\\',
				"'"  => "\x5c'",
				'"'  => '\"',
				"\r" => '\r',
				"\n" => '\n',
				"\x00" => "\x5c\x00",  // NUL
				"\x1a" => "\x5c\x1a",  // Control-Z
			]
		);

		return "'{$value}'";
	}

	/**
	 * Квотирование строки для поиска через LIKE
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	protected function quoteLike($value)
	{
		return strtr($value, ['\\' => '\\\\', '%' => '\%', '_' => '\_']);
	}

	/**
	 * Квотирование строки для поиска через LIKE
	 *
	 * @param  string $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteStringLike($value)
	{
		if (! is_scalar($value)) {
			throw new QuotationException("Некорректный тип - ожидался scalar, на входе: " . gettype($value));
		}

		return $this->quoteString( $this->quoteLike($value) );
	}

	/**
	 * Квотирование значения для поиска через REGEX
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	protected function quoteRegex($value)
	{
		return preg_quote($value, $delimiter='');
	}

	/**
	 * Квотирование значения для поиска через REGEX
	 *
	 * @param  string $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteStringRegexp($value)
	{
		if (! is_scalar($value)) {
			throw new QuotationException("Некорректный тип - ожидался scalar, на входе: " . gettype($value));
		}

		return $this->quoteString( $this->quoteRegex($value) );
	}

	/**
	 * Квотирование целого числа
	 *
	 * @param  string|int|float $value
	 *
	 * @return int
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteInt($value)
	{
		if ($value === null) {
			return 'NULL';
		}

		if (! is_scalar($value)) {
			throw new QuotationException("Некорректный тип - ожидался scalar, на входе: " . gettype($value));
		}

		return intval($value);
	}

	/**
	 * Квотирование числа с плавающей точкой. В Mysql, число с плавоющей точкой, разряд отделяется всегда "." а в PHP ","
	 *
	 * @param  string|int|float $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteFloat($value)
	{
		if ($value === null) {
			return 'NULL';
		}

		if (! is_scalar($value)) {
			throw new QuotationException("Некорректный тип - ожидался scalar, на входе: " . gettype($value));
		}

		return str_replace(',', '.', strval(floatval($value)));
	}

	/**
	 * Квотирование любого скалярного типа, либо выражения
	 *
	 * @param  string|int|float|null|\Cms\Db\Query\Expression\ExpressionInterface $value
	 *
	 * @return mixed
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteScalar($value)
	{
		if ($value === null) {
			return 'NULL';
		}

		$type = gettype($value);
		if ($this->isExpression($value)) {
			$type = 'expression';
		}
		elseif ($value instanceof \DateTime) {
			$type = 'datetime';
		}

		if (! is_scalar($value) && ! $this->isExpression($value) && ! $value instanceof \DateTime) {
			if ($type == 'object') {
				$type = get_class($value);
			}

			throw new QuotationException("Некорректный тип - ожидался scalar или expression, на входе: " . $type);
		}

		$types_map = [
			'boolean'    => self::PARAM_INT,
			'integer'    => self::PARAM_INT,
			'double'     => self::PARAM_FLOAT,
			'string'     => self::PARAM_STR,
			'datetime'   => self::PARAM_DATETIME,
			'expression' => self::PARAM_EXPRESSION,
		];

		return $this->quote($value, $types_map[$type]);
	}

	/**
	 * Квотирование идентификатора колонки
	 *
	 * @param  array|string|\Cms\Db\Query\Expression\ExpressionInterface $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteColumn($value)
	{
		if ($this->isExpression($value)) {
			return $this->quoteExpression($value);
		}

		if (is_string($value)) {
			$value = trim($value);
			if (empty($value)) {
				throw new QuotationException('Идентификатор не может быть пустой строкой');
			}

			if (strpos($value, '.') !== false) {
				return $this->quoteColumn(explode('.', $value));
			}

			if ($value == '*') {
				return $value;
			}

			return sprintf('%1$s%2$s%1$s',
				self::QUOTE_IDENTIFIER,
				$value
			);
		}

		if (is_array($value)) {
			if (Dict::isAssoc($value)) {
				$alias = key($value);

				return $this->quoteColumn($value[$alias]) . ' AS ' . $this->quoteColumn($alias);
			}
			elseif (count($value) == 2) {
				return $this->quoteColumn( $value[0] ) . '.' . $this->quoteColumn( $value[1] );
			}
		}

		throw new QuotationException("Некорректный идентификатор: " . var_export($value, true));
	}

	/**
	 * Квотирование идентификатора записи
	 *
	 * @param  int $value
	 *
	 * @return string
	 */
	protected function quoteReference($value)
	{
		$value = $this->quoteInt($value);

		return $value > 0 ? strval($value) : 'NULL';
	}

	/**
	 * Квотирование списка значений
	 *
	 * @param  array $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteList($value)
	{
		if (! is_array($value)) {
			throw new QuotationException("Некорректный тип - ожидался array, на входе: " . gettype($value));
		}

		if (empty($value)) {
			return 'NULL';
		}

		$result = '';
		foreach ($value as $item) {
			if (strlen($result)) {
				$result .= ', ';
			}

			$result .= $this->quoteScalar($item);
		}

		return $result;
	}

	/**
	 * Квотирование ассоциативного массива
	 *
	 * @param  array $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quotePairs($value)
	{
		if (! is_array($value)) {
			throw new QuotationException("Некорректный тип - ожидался array, на входе: " . gettype($value));
		}

		$result = '';
		foreach ($value as $key => $val) {
			if (strlen($result)) {
				$result .= ', ';
			}

			$result .= $this->quoteColumn($key) . '=' . $this->quoteScalar($val);
		}

		return $result;
	}

	/**
	 * Квотирование списка целых значений
	 *
	 * @param  array $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteIntList($value)
	{
		if (! is_array($value)) {
			throw new QuotationException("Некорректный тип - ожидался array, на входе: " . gettype($value));
		}

		if (empty($value)) {
			return 'NULL';
		}

		$result = '';
		foreach ($value as $item) {
			if (strlen($result)) {
				$result .= ', ';
			}

			$result .= $this->quoteInt($item);
		}

		return $result;
	}

	/**
	 * Квотирование списка идентификаторов колонок
	 *
	 * @param  array $value
	 *
	 * @return string
	 *
	 * @throws \Cms\Db\Query\Quotation\QuotationException
	 */
	protected function quoteColumnList($value)
	{
		if (! is_array($value)) {
			throw new QuotationException("Некорректный тип - ожидался array, на входе: " . gettype($value));
		}

		if (empty($value)) {
			throw new QuotationException("Некорректные данные - ожидался не пустой массив");
		}

		$result = '';
		foreach ($value as $item) {
			if (strlen($result)) {
				$result .= ', ';
			}

			$result .= $this->quoteColumn($item);
		}

		return $result;
	}
}
