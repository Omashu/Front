<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana Front Module
 * Asset interface
 */
interface Kohana_Front_Asset_Interface {

	// setters
	public function js($file, array $params = []);
	public function css($file, array $params = []);
	public function less($file, array $params = []);

	// getters
	public function get_js();
	public function get_css();
	public function get_less();

	public function render();
	public function closure($callback);
}