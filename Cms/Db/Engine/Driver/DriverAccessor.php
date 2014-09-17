<?php

namespace Cms\Db\Engine\Driver;


/**
 * Примесь для получение драйвера
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine\Driver
 */
trait DriverAccessor
{
	/**
	 * @var \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	protected $driver = null;


	/**
	 * @param \Cms\Db\Engine\Driver\DriverInterface $driver
	 */
	public function setDriver($driver)
	{
		$this->driver = $driver;
	}

	/**
	 * @return \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * @param string $query
	 */
	public function addConnectQuery($query)
	{
		$this->driver->addConnectQuery($query);
	}
}
