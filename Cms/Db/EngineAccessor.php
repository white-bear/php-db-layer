<?php

namespace Cms\Db;


/**
 * Примесь для получения слоя базы данных
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db
 */
trait EngineAccessor
{
	/**
	 * @var \Cms\Db\Engine|null
	 */
	protected $db = null;


	/**
	 * @return \Cms\Db\Engine|null
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * @param \Cms\Db\Engine $db
	 */
	public function setDb(Engine $db)
	{
		$this->db = $db;
	}
}
