<?php

namespace Cms\Utils;


/**
 * Class Text
 *
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 * @package Cms\Utils
 */
class Text
{
	private $text = '';

	static private $latin_map = [
		'А' => 'A',   'Б' => 'B', 'В' => 'V',  'Г' => 'G',  'Д' => 'D',
		'Е' => 'E',   'Ж' => 'J', 'З' => 'Z',  'И' => 'I',  'Й' => 'Y',
		'К' => 'K',   'Л' => 'L', 'М' => 'M',  'Н' => 'N',  'О' => 'O',
		'П' => 'P',   'Р' => 'R', 'С' => 'S',  'Т' => 'T',  'У' => 'U',
		'Ф' => 'F',   'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH',
		'Щ' => 'SCH', 'Ъ' => '',  'Ы' => 'YI', 'Ь' => '',   'Э' => 'E',
		'Ю' => 'YU',  'Я' => 'YA',
		'а' => 'a',   'б' => 'b', 'в' => 'v',  'г' => 'g',  'д' => 'd',
		'е' => 'e',   'ж' => 'j', 'з' => 'z',  'и' => 'i',  'й' => 'y',
		'к' => 'k',   'л' => 'l', 'м' => 'm',  'н' => 'n',  'о' => 'o',
		'п' => 'p',   'р' => 'r', 'с' => 's',  'т' => 't',  'у' => 'u',
		'ф' => 'f',   'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh',
		'щ' => 'sch', 'ъ' => 'y', 'ы' => 'yi', 'ь' => '',   'э' => 'e',
		'ю' => 'yu',  'я' => 'ya'
	];


	/**
	 * @param string $text
	 */
	public function __construct($text)
	{
		$this->text = $text;
	}

	/**
	 * @param  string $text
	 *
	 * @return \Cms\Utils\Text
	 */
	public static function fromString($text)
	{
		return new self($text);
	}

	/**
	 * @return \Cms\Utils\Text
	 */
	public function camelCase()
	{
		$text = ucfirst($this->text);

		preg_match_all('~_([a-z])~', $text, $matches, PREG_OFFSET_CAPTURE);
		foreach (array_reverse($matches[1]) as $match) {
			list($char, $pos) = $match;
			$text = substr($text, 0, $pos - 1) . strtoupper($char) . substr($text, $pos + 1);
		}

		$this->text = $text;

		return $this;
	}

	/**
	 * @param  array|string $search
	 * @param  array|string $replace
	 *
	 * @return \Cms\Utils\Text
	 */
	public function replace($search, $replace)
	{
		$this->text = str_replace($search, $replace, $this->text);

		return $this;
	}

	/**
	 * @return \Cms\Utils\Text
	 */
	public function latin()
	{
		$this->text = strtr($this->text, self::$latin_map);

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->text;
	}
}
 