php-db-layer
============

Обертка для работы с базой данных. Возможно подключение к разным базам данных, на текущий момент реализована работа с mysql.
Экранирование данных выполняется на стороне клиента при помощи указания типа входных данных:

    - ?  любой скалярный тип (автоопределение вида экранирования, применять нежелательно)
    - ?d целое число
    - ?f число с плавающей точкой
    - ?S строка
    - ?s без экранирования
    - ?x без экранирования, выполняется разрешение выражения
    - ?e строка экранируется для поиска подстроки через LIKE
    - ?E строка экранируется для поиска подстроки через REGEXP
    - ?i экранирование идентификатора
    - ?n целое число, либо null (ссылка на другую таблицу)
    - ?l массив скалярных типов
    - ?a ассоциативный массив, экранируется в виде идентификатор = скаляр
    - ?r массив идентификаторов
    - ?I массив целых чисел

Также имеется конструктор запросов, позволяющий составлять запросы:
- SELECT ... FROM ... [WHERE ...] [GROUP BY ... [HAVING ...]] [ORDER BY ...] [LIMIT ...] [FOR UPDATE]


        $db->getQueryBuilder()
            ->select()
            ->from('users')
            ->columns(['id', 'name'])
            ->where('?i = ?d', ['id', 1])
            ->groupBy(['id'])
            ->having('?i < ?d', ['id', 2])
            ->orderBy(['id'])
            ->limit(1);

- REPLACE INTO ... VALUES ...


        $db->getQueryBuilder()
            ->replace()
            ->into('users')
            ->values(['id' => 1, 'name' => 'Alex']);

- REPLACE INTO ... SET ...


        $db->getQueryBuilder()
            ->replace()
            ->into('users')
            ->mode('update')
            ->values(['id' => 1, 'name' => 'Alex']);

- UPDATE ... SET ... [WHERE ...] [ORDER BY ...] [LIMIT ...]


        $db->getQueryBuilder()
            ->update('users')
            ->values(['name' => 'Alex'])
            ->where('?i = ?d', ['id', 1])
            ->orderBy(['id'])
            ->limit(1);

- DELETE FROM ... [WHERE ...] [ORDER BY ...] [LIMIT ...]


        $db->getQueryBuilder()
            ->delete()
            ->from('users')
            ->where('?i = ?d', ['id', 1])
            ->orderBy(['id'])
            ->limit(1);

- INSERT INTO ... VALUES (...) [ON DUPLICATE KEY UPDATE ...]


        $db->getQueryBuilder()
            ->insert()
            ->into('users')
            ->values(['id' => 1, 'name' => 'Alex'])
            ->updateOnDuplicate(['name' => 'Alex']);

- INSERT INTO ... VALUES (...), (...), ... [ON DUPLICATE KEY UPDATE ...]


        $db->getQueryBuilder()
            ->insert()
            ->into('users')
            ->valuesList([['id' => 1, 'name' => 'Alex'], ['id' => 2, 'name' => 'Kenny']])
            ->updateOnDuplicate(['name' => $db->getQueryBuilder()->expressions()->Values('name')]);

Конструктор запросов позволяет конструировать очень сложные запросы, например:

    $qb = $db->getQueryBuilder()
    $select = $qb
        ->select()
        ->from('values')
        ->columns([
            'key',
            $qb->expression()->Max('value'),
            $qb->expression()->Query('?S', ['2014-09-01 10:00:00']),
        ])
        ->where('?i >= ?S', ['created_at', '2014-09-01 10:00:00'])
        ->groupBy(['key']);
    $insert = $qb
        ->insert()
        ->into('archive')
        ->columns(['key', 'value', 'created_at'])
        ->fromSelect($select)
        ->updateOnDuplicate([
            'value'      => $qb->expression()->Values('value'),
            'created_at' => $qb->expression()->Values('created_at'),
        ]);

    Формирует запрос следующего вида:

        INSERT INTO `archive`
            (`key`, `value`, `created_at`)
        SELECT
            `key`, MAX(`value`), '2014-09-01 10:00:00'
        FROM `values`
        WHERE
            `created_at` >= '2014-09-01 10:00:00'
        GROUP BY `key`
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `created_at` = VALUES(`created_at`)


Требования
----------

- PHP версии 5.4+
