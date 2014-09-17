<?php

namespace Cms\Db\Statement\Result;


/**
 * Базовая реализация результата выполнения запроса
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Db\Statement\Result
 */
abstract class BaseResult implements ResultInterface
{
	protected $pos = 0;

	protected $result = [];


	/**
	 * Извлечение данных
	 *
	 * @return array|null
	 */
	abstract protected function fetch();

	/**
	 * Возврат указателя на начало результата
	 */
	public function rewind()
	{
		$this->pos = 0;

		$data = $this->fetch();
		if ($data !== null) {
			$this->result[ $this->pos ] = $data;
		}
	}

	/**
	 * Текущее значение
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->result[ $this->pos ];
	}

	/**
	 * Получение ключа для текущей позиции
	 *
	 * @return int
	 */
	public function key()
	{
		return $this->pos;
	}

	/**
	 * Перевод указателя на следующее значение
	 */
	public function next()
	{
		$this->pos++;
		if ($this->valid()) {
			return;
		}

		$data = $this->fetch();
		if ($data !== null) {
			$this->result[ $this->pos ] = $data;
		}
	}

	/**
	 * Проверка, существует ли запись для текущего указателя
	 *
	 * @return bool
	 */
	public function valid()
	{
		return array_key_exists($this->pos, $this->result);
	}

	/**
	 * Получение первой записи из результата
	 *
	 * @return mixed|null
	 */
	public function first()
	{
		$this->rewind();

		return $this->valid() ? $this->current() : null;
	}
}
