<?php

namespace Cms\Db\Query;

use Cms\Db\Query\Quotation\QuotationAccessor;
use Cms\Db\Query\Placeholder\PlaceholderEngine,
	Cms\Db\Query\Placeholder\PlaceholderAccessor;
use Cms\Db\Query\Expression\ExpressionFactory;
use Cms\Db\Sql;


/**
 * Построитель запросов
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query
 */
class QueryBuilder
{
	use QuotationAccessor;
	use PlaceholderAccessor;

	protected $params = [];

	/** @var \Cms\Db\Query\Expression\ExpressionFactory */
	protected $factory = null;

	static protected $expressions_ns = 'Cms\Db\Query\Expression';


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
	 * Получение движка для подстановки значений в запросы
	 *
	 * @return \Cms\Db\Query\Placeholder\PlaceholderEngine
	 */
	public function getPlaceholderEngine()
	{
		if ($this->placeholder_engine === null) {
			$this->placeholder_engine = new PlaceholderEngine();

			$this->placeholder_engine->setQuotation($this->getQuotation());
		}

		return $this->placeholder_engine;
	}

	/**
	 * Сбор запроса
	 *
	 * @param  string|\Cms\Db\Sql\SqlInterface $query
	 *
	 * @return string
	 */
	public function assemble($query)
	{
		if (empty($this->params)) {
			return $query;
		}

		$placeholder_engine = $this->getPlaceholderEngine();
		$placeholder_engine->setParams($this->params);

		return $placeholder_engine->bindParams($query);
	}

	/**
	 * Получение sql запроса select
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\Select
	 */
	public function select($table='')
	{
		return new Sql\Select($table);
	}

	/**
	 * Получение sql запроса insert
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\Insert
	 */
	public function insert($table='')
	{
		return new Sql\Insert($table);
	}

	/**
	 * Получение sql запроса update
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\Update
	 */
	public function update($table='')
	{
		return new Sql\Update($table);
	}

	/**
	 * Получение sql запроса replace
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\Replace
	 */
	public function replace($table='')
	{
		return new Sql\Replace($table);
	}

	/**
	 * Получение sql запроса delete
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\Delete
	 */
	public function delete($table='')
	{
		return new Sql\Delete($table);
	}

	/**
	 * Получение sql запроса delete
	 *
	 * @param  string      $table
	 * @param  string|null $type
	 *
	 * @return \Cms\Db\Sql\Join
	 */
	public function join($table, $type=null)
	{
		return new Sql\Join($table, $type);
	}

	/**
	 * Получение sql выражения
	 *
	 * @return \Cms\Db\Query\Expression\ExpressionFactory
	 */
	public function expression()
	{
		if ($this->factory === null) {
			$this->factory = new ExpressionFactory();
			$this->factory->setPlaceholderEngine($this->getPlaceholderEngine());
		}

		return $this->factory;
	}
}
