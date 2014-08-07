<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana Front Module
 * Meta interface
 */
interface Kohana_Front_Meta_Interface {

	// setters
	public function title();
	public function description();
	public function keywords();
	public function custom($el, array $attrs = []);

	// getters
	public function get_title($separator = ' - ', $reverse = true);
	public function get_description($take_the_title = true, $reverse = true);
	public function get_keywords($take_the_title = true, $take_the_description = true);

	public function render();
	public function closure($callback);
}