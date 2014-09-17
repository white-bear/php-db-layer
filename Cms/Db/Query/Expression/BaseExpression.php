<?php

namespace Cms\Db\Query\Expression;

use Cms\Db\Query\Placeholder\PlaceholderAccessor;


/**
 * Базовоя реализация sql выражений
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
abstract class BaseExpression implements ExpressionInterface
{
	use PlaceholderAccessor;

	/**
	 * @var string
	 */
	protected $alias = '';


    /**
     * Проверка корректности выражения
     * 
     * @return bool
     */
    public function validate()
    {
        return true;
    }

	/**
	 * Задание алиаса для выражения
	 *
	 * @param  string $alias
	 *
	 * @return \Cms\Db\Query\Expression\ExpressionInterface
	 */
	public function alias($alias)
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Получение указанного алиаса
	 *
	 * @return string
	 */
	public function getAlias()
	{
		return $this->alias;
	}
}
