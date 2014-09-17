<?php

namespace Cms\Db\Meta;


/**
 * trait MetaAccessor
 * @package Cms\Db\Meta
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
trait MetaAccessor
{
	/**
	 * @var \Cms\Db\Meta\MetaInterface|null
	 */
	protected $meta = null;


	/**
	 * @param \Cms\Db\Meta\MetaInterface $meta
	 */
	public function setMeta($meta)
	{
		$this->meta = $meta;
	}

	/**
	 * @return \Cms\Db\Meta\MetaInterface|null
	 */
	public function getMeta()
	{
		return $this->meta;
	}
}
