<?php

namespace Cms\Db\Query\Quotation;


/**
 * Примесь для получения движка квотирования
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Query\Quotation
 */
trait QuotationAccessor
{
	/**
	 * @var \Cms\Db\Query\Quotation\QuotationInterface|null
	 */
	protected $quotation = null;


	/**
	 * Установка движка квотирования
	 *
	 * @param \Cms\Db\Query\Quotation\QuotationInterface $quotation
	 */
	public function setQuotation($quotation)
	{
		$this->quotation = $quotation;
	}

	/**
	 * Получение движка квотирования
	 *
	 * @return \Cms\Db\Query\Quotation\QuotationInterface|null
	 */
	public function getQuotation()
	{
		return $this->quotation;
	}
}
