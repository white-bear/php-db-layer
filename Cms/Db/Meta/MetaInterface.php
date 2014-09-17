<?php

namespace Cms\Db\Meta;

use Cms\Db\Engine;


/**
 * Class MetaInterface
 * @package Cms\Db\Meta
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
interface MetaInterface
{
	/**
	 * @return \Cms\Db\Engine|null
	 */
	public function getDb();

	/**
	 * @param \Cms\Db\Engine $db
	 */
	public function setDb(Engine $db);

	/**
	 * @return string
	 */
	public function getDatabase();

	/**
	 * @param  array $tables
	 *
	 * @return array
	 */
	public function getTablesMeta(array $tables=[]);

	/**
	 * @param  string $table
	 *
	 * @return array
	 */
	public function getColumnsMeta($table);

	/**
	 * @param  string $table
	 *
	 * @return array
	 */
	public function getReferences($table);
}
