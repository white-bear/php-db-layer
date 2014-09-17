<?php

namespace Cms\Db\Sql;


interface SqlInterface
{
	const
		SELECT_ALL         = '',
		SELECT_DISTINCT    = 'DISTINCT',
		SELECT_DISTINCTROW = 'DISTINCTROW';

	const
		MODE_UPDATE = 'update',
		MODE_INSERT = 'insert';

	/**
	 * Формирование результирующего SQL выражение
	 *
	 * @return string
	 */
	public function getSql();

	/**
	 * Получение параметров запроса
	 *
	 * @return array
	 */
	public function getParams();

	/**
	 * Указание, в какую таблицу будет производиться вставка
	 *
	 * @param  string $table
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function into($table);

	/**
	 * Указание, из какой таблицы будет происходить выборка данных
	 *
	 * @param  array|string $table   Имя таблицы, опционально указание алиаса
	 * @param  array        $columns Список выбираемых колонок
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function from($table, array $columns=[]);

	/**
	 * Указание, какие колонки будут выбираться
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function columns(array $columns=[]);

	/**
	 * Указание, какие колонки как будут квотироваться
	 *
	 * @param  array $placeholders
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function placeholders(array $placeholders);

	/**
	 * Добавление вставляемых значений
	 *
	 * @param  array $values
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function values(array $values=[]);

	/**
	 * Добавление значений для массовой вставки
	 *
	 * @param  array $values
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function valuesList(array $values=[]);

	/**
	 * Изменение приоритета запроса
	 *
	 * @param  string $priority
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function priority($priority='low');

	/**
	 * @param  \Cms\Db\Sql\SqlInterface|null $select
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function fromSelect(SqlInterface $select=null);

	/**
	 * Запрос с игнорированием ошибок
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function ignore();

	/**
	 * Указание условий запроса
	 *
	 * @param  string $where
	 * @param  array $params
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function where($where, array $params=[]);

	/**
	 * @param  \Cms\Db\Sql\Join $join
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function join(Join $join);

	/**
	 * Сортировка результатов
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function orderBy(array $columns=[]);

	/**
	 * Ограничение количества выбираемых результатов
	 *
	 * @param  int $limit
	 * @param  int $offset
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function limit($limit, $offset=0);

	/**
	 * Выполнение запроса без пересчета индексов
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function quick();

	/**
	 * Выполнение обновления записей в случае, если запись уже существует при вставке
	 *
	 * @param  array       $update_params
	 * @param  string|null $pk_name
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function updateOnDuplicate(array $update_params=[], $pk_name=null);

	/**
	 * Блокирование записей на чтение
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 *
	 * @throws \LogicException
	 */
	public function forUpdate();

	/**
	 * Режим выполнения запроса - обновление или вставка
	 *
	 * @param  string $mode
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function mode($mode='update');

	/**
	 * Выбор только уникальных результатов
	 *
	 * @param  string $distinct
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function distinct($distinct='distinct');

	/**
	 * Выполнение запроса с отключенным кешем на уровне БД
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function noCache();

	/**
	 * Выбор количества строк, соответствующих указанным условиям
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function calcFoundRows();

	/**
	 * Группировка записей
	 *
	 * @param  array $columns
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function groupBy(array $columns=[]);

	/**
	 * Условия для отсечения по аггрегациям
	 *
	 * @param  string $having
	 * @param  array  $params
	 *
	 * @return \Cms\Db\Sql\SqlInterface
	 */
	public function having($having, array $params=[]);
}
