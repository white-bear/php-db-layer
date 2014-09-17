<?php

namespace Cms\Db\Query\Expression;

/**
 * sql выражение Null
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Expression
 */
class NullExpression extends BaseExpression
{
	/**
	 * Приведение выражения к строковому представлению
	 *
	 * @return string
	 */
    public function __toString()
	{
        return 'NULL';
    }
}
