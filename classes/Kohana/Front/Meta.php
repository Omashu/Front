<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana Front Module
 * 
 * Meta data:
 * 	title, description, keywords and custom meta tags
 */
class Kohana_Front_Meta implements Kohana_Front_Meta_Interface, Kohana_Front_Interface {

	/**
	 * @var array configuration
	 */
	protected $_config = [
			'title' => [
				'separator' => ' / ',
				'reverse' => TRUE,
			],
			'description' => [
				'take_the_title' => TRUE,
				'reverse' => TRUE,
			],
			'keywords' => [
				'take_the_title' => TRUE,
				'take_the_description' => TRUE,
			],
	];

	/**
	 * @var array titles
	 */
	protected $_title = [];

	/**
	 * @var array descriptions
	 */
	protected $_description = [];

	/**
	 * @var array keywords
	 */
	protected $_keywords = [];

	/**
	 * @var array custom tags
	 */
	protected $_custom = [];

	/**
	 * Add page title
	 * @param string|array
	 * @return this
	 */
	public function title() {
		return $this->add(__FUNCTION__, func_get_args());
	}

	/**
	 * Add page description
	 * @param string|array
	 * @return this
	 */
	public function description() {
		return $this->add(__FUNCTION__, func_get_args());
	}

	/**
	 * Add page keywords
	 * @param string|array
	 * @return this
	 */
	public function keywords() {
		return $this->add(__FUNCTION__, func_get_args());
	}

	/**
	 * Add custom tag
	 * @param string $el element tag, "meta" or "link"...
	 * @return this
	 */
	public function custom($el, array $attrs = []) {
		$this->_custom[] = [$el, $attrs];
		return $this;
	}

	/**
	 * Helper for set canonical tag
	 * @param string $href canonical url
	 * @return this
	 */
	public function canonical($href) {
		return $this->custom("link", ["rel" => "canonical", "href" => $href]);
	}

	/**
	 * Internal helper for add title, description, keywords
	 * @param string $func
	 * @param array $values
	 * @return this
	 */
	protected function add($func, array $values = []) {
		foreach ($values as $value) {
			if (is_array($value)) {
				$this->$func($value);
				continue;
			}

			$this->{"_".$func}[] = $value;
		}

		return $this;
	}

	/**
	 * Closure method for testing or crutches :)
	 * @param function $callback
	 * @return your callback
	 */
	public function closure($callback) {
		return $callback($this);
	}

	/**
	 * Prepare and get title
	 * @param string $separator
	 * @param bool $reverse reverse order
	 * @return string
	 */
	public function get_title($separator = ' - ', $reverse = true) {
		$title = $this->_title;

		array_walk($title, function(&$value) {
			$value = trim(HTML::chars($value));
		});

		if ($reverse) {
			$title = array_reverse($title);
		}

		$title = implode($separator, $title);
		return $title;
	}

	/**
	 * Prepare and get description
	 * @param bool $take_the_title append page title in description
	 * @param bool $reverse reverse order
	 * @return string
	 */
	public function get_description($take_the_title = true, $reverse = true) {
		$description = $this->_description;

		array_walk($description, function(&$value) {
			$value = trim(HTML::chars($value));
		});

		if ($reverse === true) {
			$description = array_reverse($description);
		}

		$description = implode('. ', $description).". ";

		if ($take_the_title === true) {
			$description .= " " . $this->get_title(". ") . " ";
		}

		$description = mb_substr($description, 0, 250);
		$description = preg_replace("/\s+/s", " ", $description);
		$description = trim($description);
		return $description;
	}

	/**
	 * Prepare and get keywords
	 * @param bool $take_the_title append page title in keywords
	 * @param bool $take_the_description append page description in keywords
	 * @return string
	 */
	public function get_keywords($take_the_title = true, $take_the_description = true) {
		$keywords = $this->_keywords;

		array_walk($keywords, function(&$value) {
			$value = trim(HTML::chars($value));
		});

		if ($take_the_title) {
			$keywords[] = $this->get_title();
		}

		if ($take_the_description) {
			$keywords[] = str_replace(".", ",", $this->get_description());
		}

		$temp_data = implode(', ', $keywords);

		$clean_data = mb_convert_encoding($temp_data, "UTF-8");
		$clean_data = mb_strtolower($clean_data);
		$clean_data = preg_replace("/[^а-яёa-z0-9,]/u", " ", $clean_data);
		$clean_data = preg_replace("/(\s+)/", " ", $clean_data);

		// разбиваем на слова
		$array_keywords = explode(",", $clean_data);
		array_walk($array_keywords, function(&$value) {
			$value = trim($value);
		});

		// собираем часто повторяющиеся слова
		$frequent_keywords = array();

		foreach ($array_keywords as $keyword) {
			if (isset($frequent_keywords[$keyword])) {
				$frequent_keywords[$keyword]++;
				continue;
			}

			$frequent_keywords[$keyword] = 1;
		}

		arsort($frequent_keywords);

		if (count($frequent_keywords)>20) {
			$min_length = 6;
		} else if (count($frequent_keywords)>15) {
			$min_length = 5;
		} else if (count($frequent_keywords)>10) {
			$min_length = 4;
		} else {
			$min_length = 3;
		}

		$new_array_keywords = array();
		foreach ($frequent_keywords as $keyword => $iter) {
			$length = mb_strlen($keyword);

			if ($length >= $min_length and !isset($new_array_keywords[$keyword])) {
				$new_array_keywords[$keyword] = 1 + $iter;
			} else if ($length >= $min_length) {
				$new_array_keywords[$keyword]++;
			}
		}

		arsort($new_array_keywords);

		$ready_keywords = array();
		foreach ($new_array_keywords as $keyword => $count) {
			$ready_keywords[] = trim($keyword);
			if (count($ready_keywords) > 16) {
				break;
			}
		}

		$keywords = implode(", ", $ready_keywords);
		$keywords = trim($keywords);
		return $keywords;
	}

	/**
	 * Get html
	 * @return string
	 */
	public function render() {
		$html = '<title>'.$this->get_title($this->_config["title"]["separator"], $this->_config["title"]["reverse"]).'</title>'."\n";
		$html .= '<meta name="description" content="'.$this->get_description($this->_config["description"]["take_the_title"], $this->_config["description"]["reverse"]).'">'."\n";
		$html .= '<meta name="keywords" content="'.$this->get_keywords($this->_config["keywords"]["take_the_title"], $this->_config["keywords"]["take_the_description"]).'">';

		foreach ($this->_custom as $custom) {
			$html .= "\n<{$custom[0]}".HTML::attributes($custom[1])."/>";
		}

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