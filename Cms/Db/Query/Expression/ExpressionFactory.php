<?php

namespace Cms\Db\Query\Expression;

use Cms\Db\Query\Placeholder\PlaceholderAccessor;


/**
 * Class ExpressionFactory
 * @package Cms\Db\Query\Expression
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class ExpressionFactory
{
	use PlaceholderAccessor;


	/**
	 * @param  string $expression_name
	 * @param  array  $args
	 *
	 * @return \Cms\Db\Query\Expression\ExpressionInterface
	 */
	private function getExpression($expression_name, array $args=[])
	{
		$class_name = 'Cms\Db\Query\Expression\\' . $expression_name . 'Expression';

		$reflection = new \ReflectionClass($class_name);

		/** @var \Cms\Db\Query\Expression\ExpressionInterface $instance */
		$instance = $reflection->hasMethod('__construct') ?
			$reflection->newInstanceArgs($args) :
			$reflection->newInstance();

		$instance->setPlaceholderEngine($this->getPlaceholderEngine());

		return $instance;
	}

	/**
	 * @param  string $column
	 *
	 * @return \Cms\Db\Query\Expression\CountExpression
	 */
	public function Count($column='*')
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string|int|\DateTime $date
	 *
	 * @return \Cms\Db\Query\Expression\DateExpression
	 */
	public function Date($date)
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string $column
	 *
	 * @return \Cms\Db\Query\Expression\MaxExpression
	 */
	public function Max($column)
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string $interval
	 *
	 * @return \Cms\Db\Query\Expression\NowExpression
	 */
	public function Now($interval='')
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @return \Cms\Db\Query\Expression\NullExpression
	 */
	public function Null()
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string $query
	 * @param  array  $params
	 *
	 * @return \Cms\Db\Query\Expression\QueryExpression
	 */
	public function Query($query, array $params=[])
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string $column
	 *
	 * @return \Cms\Db\Query\Expression\SumExpression
	 */
	public function Sum($column)
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}

	/**
	 * @param  string $column
	 *
	 * @return \Cms\Db\Query\Expression\ValuesExpression
	 */
	public function Values($column)
	{
		return $this->getExpression(__FUNCTION__, func_get_args());
	}
}
