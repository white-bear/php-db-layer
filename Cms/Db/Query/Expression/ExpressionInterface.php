<?php

namespace Cms\Db\Query\Expression;


/**
 * Интерфейс sql выражения
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
interface ExpressionInterface
{
    /**
	 * Проверка корректности выражения
	 *
     * @return boolean
     */
    public function validate();

	/**
	 * Задание алиаса для выражения
	 *
	 * @param string $alias
	 */
	public function alias($alias);

	/**
	 * Получение указанного алиаса
	 *
	 * @return string
	 */
	public function getAlias();

	/**
	 * Установка движка для подстановки значений в запросы
	 *
	 * @param \Cms\Db\Query\Placeholder\PlaceholderEngine $placeholder_engine
	 */
	public function setPlaceholderEngine($placeholder_engine);

	/**
	 * Получение движка для подстановки значений в запросы
	 *
	 * @return \Cms\Db\Query\Placeholder\PlaceholderEngine|null
	 */
	public function getPlaceholderEngine();
}
