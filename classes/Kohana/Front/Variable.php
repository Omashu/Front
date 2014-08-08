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