<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Front {
	/**
	 * @var object Kohana_Front_Meta
	 */
	protected $_meta;

	/**
	 * @var object Kohana_Front_Asset
	 */
	protected $_asset;

	/**
	 * @var object Kohana_Front_Variable
	 */
	protected $_variable;

	/**
	 * @var object Config_Group
	 */
	protected $_config;

	/**
	 * @var object Kohana_Front
	 */
	protected static $_instance;

	// this is spar..singleton
	final private function __construct(){}

	/**
	 * Get signleton
	 * @return this
	 */
	public static function instance() {
		if (!isset(Front::$_instance)) {
			Front::$_instance = new Front();
			Front::$_instance->_config = Kohana::$config->load("front");

			if (!isset(Front::$_instance->_config->configure)) {
				Front::$_instance->_config->configure = array();
			}

			if (!isset(Front::$_instance->_config->apply)) {
				Front::$_instance->_config->apply = array();
			}

			Front::$_instance->apply(Front::$_instance->_config->apply);
		}

		return Front::$_instance;
	}

	/**
	 * Get meta object
	 * @return object Kohana_Front_Meta
	 */
	public static function meta() {
		if (!isset(Front::instance()->_meta)) {
			Front::instance()->_meta = new Front_Meta();
			Front::instance()->_meta->configure(Arr::get(Front::instance()->_config->configure, "meta", array()));
		}

		return Front::instance()->_meta;
	}

	/**
	 * Get asset object
	 * @return object Kohana_Front_Asset
	 */
	public static function asset() {
		if (!isset(Front::instance()->_asset)) {
			Front::instance()->_asset = new Front_Asset();
			Front::instance()->_asset->configure(Arr::get(Front::instance()->_config->configure, "asset", array()));
		}

		return Front::instance()->_asset;
	}

	/**
	 * Get variable object
	 * @return object Kohana_Front_Variable
	 */
	public static function variable() {
		if (!isset(Front::instance()->_variable)) {
			Front::instance()->_variable = new Front_Variable();
			Front::instance()->_variable->configure(Arr::get(Front::instance()->_config->configure, "variable", array()));
		}

		return Front::instance()->_variable;
	}

	/**
	 * Apply config values
	 * @param array $apply values
	 * @return this
	 */
	protected function apply(array $apply = []) {
		foreach ($apply as $section => $section_values) {
			foreach ($section_values as $method => $values) {
				if ($section === "meta" AND in_array($method, array("title", "description", "keywords", "custom"))) {
					call_user_func_array(array(Front::$section(), $method), $values);
				} else if ($section === "asset" AND in_array($method, array("js", "css", "less"))) {
					foreach ($values as $value) {
						call_user_func_array(array(Front::$section(), $method), $value);
					}
				} else if ($section === "variable" AND in_array($method, array("lang", "jsvar"))) {
					foreach ($values as $value) {
						call_user_func_array(array(Front::$section(), $method), $value);
					}
				}
			}
		}

		return $this;
	}
}