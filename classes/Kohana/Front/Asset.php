<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Kohana Front Module
 * 
 * Assets:
 * 	js, css, less
 */
class Kohana_Front_Asset implements Kohana_Front_Asset_Interface, Kohana_Front_Interface {

	/**
	 * @var array configuration
	 */
	protected $_config = [
		'cache_paths' => NULL,
		'paths' => NULL,
		'priority' => 1000,
	];

	/**
	 * @var array js group by priority
	 */
	protected $_js = [];

	/**
	 * @var array css group by priority
	 */
	protected $_css = [];

	/**
	 * @var array less group by priority
	 */
	protected $_less = [];

	/**
	 * Add js
	 * @param string $file URL or path to find your script
	 * @param array $params read README.md to see all options
	 * @return this
	 */
	public function js($file, array $params = []) {
		$params = $this->merge_params($params);

		// if external?
		if ($params["external"] OR preg_match("/\/\//", $file)) {
			$params["external"] = TRUE;
			$this->_js[$params["priority"]][] = [$file, $params];
			return $this;
		}

		// append .js
		if (!preg_match("/\.js$/i", $file)) {
			$file .= ".js";
		}

		// if no path (one word), set js/ folder
		if (!preg_match("/\//", $file)) {
			$file = "js/".$file;
		}

		// complete
		$this->_js[$params["priority"]][] = [$file, $params];
		return $this;
	}

	/**
	 * Add css
	 * @param string $file URL or path to find your style
	 * @param array $params read README.md to see all options
	 * @return this
	 */
	public function css($file, array $params = []) {
		$params = $this->merge_params($params);

		// if external?
		if ($params["external"] OR preg_match("/\/\//", $file)) {
			$params["external"] = TRUE;
			$this->_css[$params["priority"]][] = [$file, $params];
			return $this;
		}

		// append .css
		if (!preg_match("/\.css$/i", $file)) {
			$file .= ".css";
		}

		// if no path (one word), set css/ folder
		if (!preg_match("/\//", $file)) {
			$file = "css/".$file;
		}

		// complete
		$this->_css[$params["priority"]][] = [$file, $params];
		return $this;
	}

	/**
	 * Add less
	 * @param string $file URL or path to find your less
	 * @param array $params read README.md to see all options
	 * @return this
	 */
	public function less($file, array $params = []) {
		$params = $this->merge_params($params);

		// if external?
		if ($params["external"] OR preg_match("/\/\//", $file)) {
			$params["external"] = TRUE;
			$this->_less[$params["priority"]][] = [$file, $params];
			return $this;
		}

		// append .less
		if (!preg_match("/\.less$/i", $file)) {
			$file .= ".less";
		}

		// if no path (one word), set less/ folder
		if (!preg_match("/\//", $file)) {
			$file = "less/".$file;
		}

		// complete
		$this->_less[$params["priority"]][] = [$file, $params];
		return $this;
	}

	/**
	 * Build and get scripts
	 * @return array
	 */
	public function get_js() {
		$js = $this->build_paths($this->_js);

		// cache
		if ($this->_config["cache_paths"]) {
			$new_js = array();

			$cache_content = "";
			$cache_file = $js["cache_key"].".js";
			$path = $this->_config["cache_paths"]["path"].$cache_file;
			$http = $this->_config["cache_paths"]["http"].$cache_file;
			unset($js["cache_key"]);

			if (file_exists($path)) {
				$new_js[] = [$http, $this->merge_params([])];
			} else {
				foreach ($js as $key => $values) {
					if (!$values[1]["merge"]) {
						$new_js[] = $values;
					}

					$cache_content .= ";".file_get_contents($values[0]).";";
				}

				$fp = fopen($path, "w");
				fwrite($fp, $cache_content);
				fclose($fp);

				$new_js[] = [$http, $this->merge_params([])];
			}

			return $new_js;
		}

		return $js;
	}

	/**
	 * Build and get styles
	 * @return array
	 */
	public function get_css() {
		$css = $this->build_paths($this->_css);

		// cache
		if ($this->_config["cache_paths"]) {
			$new_css = array();
			
			$cache_content = "";
			$cache_file = $css["cache_key"].".css";
			$path = $this->_config["cache_paths"]["path"].$cache_file;
			$http = $this->_config["cache_paths"]["http"].$cache_file;
			unset($css["cache_key"]);

			if (file_exists($path)) {
				$new_css[] = [$http, $this->merge_params([])];
			} else {
				foreach ($css as $key => $values) {
					if (!$values[1]["merge"]) {
						$new_css[] = $values;
					}

					$cache_content .= ";".file_get_contents($values[0]).";";
				}

				$fp = fopen($path, "w");
				fwrite($fp, $cache_content);
				fclose($fp);

				$new_css[] = [$http, $this->merge_params([])];
			}

			return $new_css;
		}

		return $css;
	}

	/**
	 * Build and get less
	 * @return array
	 */
	public function get_less() {
		$less = $this->build_paths($this->_less);
		return $less;
	}

	/**
	 * Closure method for testing or crutches :)
	 * @param function $callback
	 * @return your callback
	 */
	public function closure($callback) {
		return $callback($this);
	}

	public function render() {
		$html = "";

		// html js
		$js = $this->get_js();
		foreach ($js as $key => $values) {
			if (!is_numeric($key)) {
				continue;
			}

			$html .= $values[1]["before"] . HTML::script($values[0]) . $values[1]["after"];
		}

		// html css
		$css = $this->get_css();
		foreach ($css as $key => $values) {
			if (!is_numeric($key)) {
				continue;
			}

			$html .= $values[1]["before"] . HTML::style($values[0]) . $values[1]["after"];
		}

		// html less
		$less = $this->get_less();
		foreach ($less as $key => $values) {
			if (!is_numeric($key)) {
				continue;
			}

			$html .= $values[1]["before"] . HTML::style($values[0], ['rel' => 'stylesheet/less']) . $values[1]["after"];
		}

		return $html;
	}

	public function __toString() {
		return $this->render();
	}

	/**
	 * Configuration
	 */
	public function configure(array $params = []) {
		if (isset($params["cache_paths"])) {
			if (!isset($params["cache_paths"]["path"]) OR !isset($params["cache_paths"]["http"])) {
				throw new Front_Exception("Invalid cache paths");
			}

			$params["cache_paths"]["path"] = rtrim($params["cache_paths"]["path"], DIRECTORY_SEPARATOR);
			$params["cache_paths"]["path"] .= DIRECTORY_SEPARATOR;

			$params["cache_paths"]["http"] = rtrim($params["cache_paths"]["http"], DIRECTORY_SEPARATOR);
			$params["cache_paths"]["http"] .= DIRECTORY_SEPARATOR;

			if (!file_exists($params["cache_paths"]["path"])) {
				throw new Front_Exception("Cache directory not found");
			} else if (!is_writable($params["cache_paths"]["path"])) {
				throw new Front_Exception("Cache directory is not writable");
			}
		}

		$this->_config = Arr::merge($this->_config, $params);
		$this->_config["priority"] = (int) $this->_config["priority"];

		return $this;
	}

	/**
	 * Build paths on js/css/less
	 * @param array $rows js/css/less
	 * @return array files
	 */
	protected function build_paths(array $rows = []) {
		$unique = [];
		$files = [];

		ksort($rows);

		foreach ($rows as $priority => $group) {
			foreach ($group as $js) {
				$file = $js[0];
				$params = $js[1];

				if (isset($unique[$file])) {
					continue;
				}

				$unique[$file] = TRUE;

				if ($params["external"]) {
					$files[] = [$file, $params];
					continue;
				}

				$local = $this->find_file($file);
				if ($local) {
					$files[] = [$local["http"], $params];
				}
			}
		}

		$files["cache_key"] = md5("front_asset_".serialize($files));
		return $files;
	}

	/**
	 * Find a local file on your paths
	 * @param string $file relative path
	 * @return array
	 */
	protected function find_file($file) {
		if (!$this->_config["paths"]) {
			return array("http"=>$file,"path"=>$file);
		}

		foreach ($this->_config["paths"] as $path) {
			if (file_exists($path["path"].$file)) {
				return array("http"=>$path["http"].$file,"path"=>$path["path"].$file);
			}
		}

		return NULL;
	}

	protected function merge_params(array $params = []) {
		$params = Arr::merge(array(
			"before" => NULL,
			"after" => NULL,
			"merge" => TRUE,
			"priority" => $this->_config["priority"],
			"external" => FALSE,
		), $params);

		$params["before"] = (string) $params["before"];
		$params["after"] = (string) $params["after"];
		$params["merge"] = (bool) $params["merge"];
		$params["priority"] = (int) $params["priority"];
		$params["external"] = (bool) $params["external"];

		return $params;
	}
}