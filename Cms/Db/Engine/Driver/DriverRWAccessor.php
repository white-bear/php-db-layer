<?php

namespace Cms\Db\Engine\Driver;


/**
 * Примесь для получение драйверов чтения и записи
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Engine\Driver
 */
trait DriverRWAccessor
{
	use DriverAccessor;

	/**
	 * @var \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	protected $driver_write = null;


	/**
	 * @param \Cms\Db\Engine\Driver\DriverInterface $driver
	 */
	public function setDriver($driver)
	{
		$this->driver = $driver;
		$this->driver_write = $driver;
	}

	/**
	 * @param \Cms\Db\Engine\Driver\DriverInterface $driver
	 */
	public function setWriteDriver($driver)
	{
		$this->driver_write = $driver;
	}

	/**
	 * @return \Cms\Db\Engine\Driver\DriverInterface|null
	 */
	public function getWriteDriver()
	{
		return $this->driver_write;
	}

	/**
	 * @param string $query
	 */
	public function addConnectQuery($query)
	{
		$this->driver_write->addConnectQuery($query);

		if ($this->driver != $this->driver_write) {
			$this->driver->addConnectQuery($query);
		}
	}
}
