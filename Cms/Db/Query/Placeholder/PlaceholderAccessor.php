<?php

namespace Cms\Db\Query\Placeholder;


/**
 * Примесь для доступа к движку для подстановки значений в запросы
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Placeholder
 */
trait PlaceholderAccessor
{
	/**
	 * @var \Cms\Db\Query\Placeholder\PlaceholderEngine|null
	 */
	protected $placeholder_engine = null;


	/**
	 * Установка движка для подстановки значений в запросы
	 *
	 * @param \Cms\Db\Query\Placeholder\PlaceholderEngine $placeholder_engine
	 */
	public function setPlaceholderEngine($placeholder_engine)
	{
		$this->placeholder_engine = $placeholder_engine;
	}

	/**
	 * Получение движка для подстановки значений в запросы
	 *
	 * @return \Cms\Db\Query\Placeholder\PlaceholderEngine|null
	 */
	public function getPlaceholderEngine()
	{
		return $this->placeholder_engine;
	}
}
