<?php

namespace Cms\Db\Meta;

use Cms\Db\EngineAccessor;


/**
 * Class MysqliMeta
 * @package Cms\Db\Meta
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class MysqliMeta implements MetaInterface
{
	use EngineAccessor;

	static private $types_map = [
		'bool'      => 'bool',
		'boolean'   => 'bool',

		'integer'   => 'int',
		'int'       => 'int',
		'smallint'  => 'int',
		'tinyint'   => 'int',
		'mediumint' => 'int',
		'bigint'    => 'int',
		'serial'    => 'int',
		'year'      => 'int',

		'decimal'          => 'float',
		'dec'              => 'float',
		'numeric'          => 'float',
		'fixed'            => 'float',
		'float'            => 'float',
		'double'           => 'float',
		'double precision' => 'float',
		'real'             => 'float',

		'bit'              => 'string',
		'char'             => 'string',
		'national char'    => 'string',
		'varchar'          => 'string',
		'national varchar' => 'string',
		'binary'           => 'string',
		'varbinary'        => 'string',
		'tinyblob'         => 'string',
		'tinytext'         => 'string',
		'blob'             => 'string',
		'text'             => 'string',
		'mediumblob'       => 'string',
		'mediumtext'       => 'string',
		'longblob'         => 'string',
		'longtext'         => 'string',
		'enum'             => 'string',
		'set'              => 'array',

		'date'      => '\DateTime',
		'datetime'  => '\DateTime',
		'timestamp' => '\DateTime',
		'time'      => '\DateTime',
	];

	static private $length_map = [
		'tinytext'   => 255,
		'text'       => 65535,
		'mediumtext' => 16777215,
		'longtext'   => 4294967295,
		'tinyblob'   => 255,
		'blob'       => 65535,
		'mediumblob' => 16777215,
		'longblob'   => 4294967295,
	];


	/**
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->db->getDriver()->getDatabase();
	}

	/**
	 * @param  array $tables
	 *
	 * @return array
	 */
	public function getTablesMeta(array $tables=[])
	{
		$qb = $this->db->getQueryBuilder();
		$database = $this->getDatabase();

		$query = $qb
			->select('information_schema.tables')
			->where('?i = ?S', ['table_schema', $database]);
		if (count($tables)) {
			$query->where('?i IN (?l)', ['table_name', $tables]);
		}

		return $this->db->query($query)->fetchAll();
	}

	/**
	 * @param  string $table
	 *
	 * @return array
	 */
	public function getColumnsMeta($table)
	{
		$qb = $this->db->getQueryBuilder();
		$database = $this->getDatabase();

		$query = $qb
			->select('information_schema.columns')
			->where('?i = ?S', ['table_schema', $database])
			->where('?i = ?S', ['table_name', $table]);

		$result = [];
		$columns = $this->db->query($query)->fetchAll();
		foreach ($columns as $column) {
			$result[ $column['COLUMN_NAME'] ] = [
				'name'           => $column['COLUMN_NAME'],
				'type'           => $column['DATA_TYPE'],
				'php_type'       => $this->getColumnPhpTypes($column),
				'comment'        => explode("\n", $column['COLUMN_COMMENT'])[0],
				'consts'         => $this->getColumnConsts($column),
				'nullable'       => $column['IS_NULLABLE'] == 'YES',
				'default'        => $column['COLUMN_DEFAULT'],
				'auto_increment' => $column['EXTRA'] == 'auto_increment',
				'primary'        => $column['COLUMN_KEY'] == 'PRI',
				'unique'         => $column['COLUMN_KEY'] == 'PRI' || $column['COLUMN_KEY'] == 'UNI',
				'unsigned'       => strpos($column['COLUMN_TYPE'], 'unsigned') !== false,
			];

			if (isset(self::$length_map[ $column['DATA_TYPE'] ])) {
				$result[ $column['COLUMN_NAME'] ]['length'] = self::$length_map[ $column['DATA_TYPE'] ];
			}
			elseif (preg_match('~^(char|varchar|binary|varbinary)\((.+)\)$~', trim($column['COLUMN_TYPE']), $matches)) {
				$result[ $column['COLUMN_NAME'] ]['length'] = $matches[2];
			}
		}

		return $result;
	}

	/**
	 * @param  string $table
	 *
	 * @return array
	 */
	public function getReferences($table)
	{
		$qb = $this->db->getQueryBuilder();
		$database = $this->getDatabase();

		$query = $qb
			->select('information_schema.key_column_usage')
			->where('?i = ?S', ['table_schema', $database])
			->where('?i = ?S', ['table_name', $table])
			->where('?i IS NOT NULL', ['REFERENCED_COLUMN_NAME']);

		$result = [];
		$references = $this->db->query($query)->fetchAll();
		foreach ($references as $reference) {
			$result[ $reference['COLUMN_NAME'] ] = [
				'schema' => $reference['REFERENCED_TABLE_SCHEMA'],
				'table'  => $reference['REFERENCED_TABLE_NAME'],
				'column' => $reference['REFERENCED_COLUMN_NAME'],
			];
		}

		return $result;
	}

	/**
	 * @param  array $column
	 *
	 * @return array
	 */
	private function getColumnPhpTypes(array $column)
	{
		$types = [self::$types_map[ $column['DATA_TYPE'] ]];

		if (substr($column['COLUMN_NAME'], -5) == '_json') {
			$types = ['array'];
		}

		if ($column['IS_NULLABLE'] == 'YES') {
			$types []= 'null';
		}

		return $types;
	}

	/**
	 * @param  array $column
	 *
	 * @return array
	 */
	public function getColumnConsts(array $column)
	{
		$prefix = strtoupper($column['COLUMN_NAME']) . '_';
		$comment = trim($column['COLUMN_COMMENT']);
		if (strpos($comment, "\n") === false) {
			return [];
		}

		$consts = [];
		$lines = array_slice(explode("\n", $comment), 1);
		foreach ($lines as $line) {
			// @TECHNICAL=technical|Технические вопросы
			if (preg_match('~^@(?P<name>[^=]+)=(?P<value>[^|]+)\|(?P<comment>.+)$~us', $line, $matches)) {
				$consts[ $prefix . $matches['name'] ] = [
					'value'   => $matches['value'],
					'comment' => $matches['comment'],
				];
			}
		}

		return $consts;
	}
}
