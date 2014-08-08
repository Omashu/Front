<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana Front Module
 * 
 * Lang:
 * 	add js lang var and custom variables
 */
class Kohana_Front_Variable implements Kohana_Front_Interface {

	/**
	 * @var array configuration
	 */
	protected $_config = [
		'lang' => '__Lang',
		'jsvar' => '__Jsvar',
	];

	/**
	 * @var array langs
	 */
	protected $_lang = [];

	/**
	 * @var array js vars
	 */
	protected $_jsvar = [];

	/**
	 * Add lang
	 * @param string $key
	 * @param null|string|array $value
	 * @return this
	 */
	public function lang($key, $value = null) {
		if (is_null($value)) {
			$value = __($key);
		} else if (is_array($value)) {
			$value = __($key, $value);
		} else if (!is_string($value)) {
			$value = $key;
		}

		$this->_lang[$key] = $value;

		return $this;
	}

	/**
	 * Add jsvar
	 * @param string $key
	 * @param mixed $value
	 * @return this
	 */
	public function jsvar($key, $value = null) {
		$explode = explode(".", $key);

		$temp_data = $this->_jsvar;
		$data = &$temp_data;

		foreach ($explode as $key) {
			if (!isset($data[$key])) {
				$data[$key] = array();
			}

			$data = &$data[$key];
		}

		$data = $value;
		$this->_jsvar = $temp_data;
		return $this;
	}

	/**
	 * Get html
	 * @return string
	 */
	public function render() {
		$html = '<script type="text/javascript">';
		$html .= "var {$this->_config["lang"]} = ".json_encode($this->_lang).";";
		$html .= "var {$this->_config["jsvar"]} = ".json_encode($this->_jsvar).";";

		// js function get lang value of object by key
		$functions = ';function __lang(key,values) {
			values = values || {};
			var value = __value(key, '.$this->_config["lang"].');

			if (value === undefined) {
				return key;
			}

			for (var key in values) {
				value = value.replace(key,values[key]);
			}

			return value;
		};';

		// js function get value of object by key
		$functions .= ';function __value(key,array) {
			var paths = key.split(".");
			array = array || '.$this->_config["jsvar"].';

			for (var key in paths) {
				var key = paths[key];
				if (array[key] === undefined) {
					return undefined;
				}

				array = array[key];
			}

			var value = array;
			return value;
		};';

		$html .= preg_replace("(\n|\t)", "", $functions);
		$html .= '</script>';

		return $html;
	}

	/**
	 * Magic
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Configuration
	 */
	public function configure(array $params = []) {
		$this->_config = Arr::merge($this->_config, $params);
		return $this;
	}
}