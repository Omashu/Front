<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Front {

	const TAG_META = "meta";
	const TAG_LINK = "link";

	/**
	 * Seo data
	 */
	protected $_title 			= array();
	protected $_description = array();
	protected $_keywords 		= array();
	protected $_custom_tags = array(
		/*
		"attributes" => array("name" => "value")
		*/
	);

	/**
	 * Media files
	 */
	protected $_style 			= array();
	protected $_script 			= array();
	protected $_less 				= array();

	/**
	 * Custom js vars
	 */
	protected $_jsdata 			= array();
	protected $_langs 			= array();

	/**
	 * array('path' => ROOT, 'www' => HTTP)
	 */
	protected $_paths 			= array();

	/**
	 * array('path' => ROOT, 'www' => HTTP)
	 */
	protected $_cache_path	= array();

	/**
	 * Main vars
	 */
	protected $_jsvar 			= "__frontVars";
	protected $_jsvar_langs = "__langs";

	/**
	 * Config object
	 */
	protected $_config = NULL;

	/**
	 * Singleton
	 */
	protected static $_instance;

	/**
	 * This object
	 * @return this instance
	 */
	public static function instance()
	{
		if (!is_object(Front::$_instance))
		{
			Front::$_instance = new Front();
			$config = Front::$_instance->_config = Kohana::$config->load('front');

			// Применяем настройки из конфига

			if (isset($config["path_static"]) AND is_array($config["path_static"]))
			{
				foreach ($config["path_static"] as $path_static)
				{
					Front::add_path($path_static);
				}
			}

			if (isset($config["path_cache"]) AND is_array($config["path_cache"]))
			{
				Front::set_cache_path($config["path_cache"]);
			}

			if (isset($config["title"]) AND (is_array($config["title"]) OR is_string($config["title"])))
			{
				if (is_array($config["title"]))
				{
					call_user_func_array(array(Front::$_instance, "add_title"), $config["title"]);
				} else
				{
					Front::add_title($config["title"]);
				}
			}

			if (isset($config["description"]) AND (is_array($config["description"]) OR is_string($config["description"])))
			{
				if (is_array($config["description"]))
				{
					call_user_func_array(array(Front::$_instance, "add_description"), $config["description"]);
				} else
				{
					Front::add_description($config["description"]);
				}
			}

			if (isset($config["keywords"]) AND (is_array($config["keywords"]) OR is_string($config["keywords"])))
			{
				if (is_array($config["keywords"]))
				{
					call_user_func_array(array(Front::$_instance, "add_keywords"), $config["keywords"]);
				} else
				{
					Front::add_keywords($config["keywords"]);
				}
			}

			if (isset($config["custom_tags"]) AND is_array($config["custom_tags"]))
			{
				foreach ($config["custom_tags"] as $custom_tag)
				{
					Front::add_custom_tag($custom_tag[0], $custom_tag[1]);
				}
			}

			if (isset($config["jsvar"]) AND is_string($config["jsvar"]))
			{
				Front::$_instance->_jsvar = $config["jsvar"];
			}

			if (isset($config["jsvar_langs"]) AND is_string($config["jsvar_langs"]))
			{
				Front::$_instance->_jsvar_langs = $config["jsvar_langs"];
			}

			if (isset($config["style"]) AND is_array($config["style"]))
			{
				foreach ($config["style"] as $style)
				{
					Front::add_style($style[0], $style[1]);
				}
			}

			if (isset($config["less"]) AND is_array($config["less"]))
			{
				foreach ($config["less"] as $less)
				{
					Front::add_less($less[0], $less[1]);
				}
			}

			if (isset($config["script"]) AND is_array($config["script"]))
			{
				foreach ($config["script"] as $script)
				{
					Front::add_script($script[0], $script[1]);
				}
			}

			if (isset($config["jsdata_vars"]) AND is_array($config["jsdata_vars"]))
			{
				foreach ($config["jsdata_vars"] as $jsdata_vars)
				{
					Front::add_jsvar($jsdata_vars[0], $jsdata_vars[1]);
				}
			}

			// Js functions, start on DOM loaded (jqeury)
			if (isset($config["jsdata_funcs"]) AND is_array($config["jsdata_funcs"]))
			{
				foreach ($config["jsdata_funcs"] as $jsdata_func)
				{
					Front::add_jsinit($jsdata_func);
				}
			}

			// Js langs
			if (isset($config["langs"]) AND is_array($config["langs"]))
			{
				foreach ($config["langs"] as $lang)
				{
					Front::add_lang($lang[0], isset($lang[1]) ? $lang[1] : null);
				}
			}
		}

		return Front::$_instance;
	}

	/**
	 * Добавить путь где искать css/js
	 * @param array $path путь, array('path' => 'прямой к папке', 'www' => 'http адрес к папке')
	 * @return this instance
	 */
	public static function add_path(array $path)
	{
		$www = Arr::get($path, 'www');
		$www = trim($www, "/") . "/";
		$path = Arr::get($path, 'path');
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if (!file_exists($path) OR !is_dir($path))
		{
			throw new Front_Exception("Directory is not found: :path", array(
				":path" => $path
			));
		}

		Front::instance()->_paths[] = array(
			'path' => $path,
			'www' => $www,
		);

		return Front::instance();
	}

	/**
	 * Установить путь для включения кеширования css/js
	 * @param array $path путь, array('path' => 'прямой к папке', 'www' => 'http адрес к папке')
	 * @return this instance
	 */
	public static function set_cache_path(array $path)
	{
		$www = Arr::get($path, 'www');
		$www = trim($www, "/") . "/";
		$path = Arr::get($path, 'path');
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if (!file_exists($path) OR !is_dir($path))
		{
			throw new Front_Exception("Directory is not found: :path", array(
				":path" => $path
			));
		}

		if (!is_writable($path))
		{
			throw new Front_Exception("Directory is not writeable: :path", array(
				":path" => $path
			));
		}

		Front::instance()->_cache_path = array(
			'path' => $path,
			'www' => $www,
		);

		return Front::instance();
	}

	/**
	 * Удаление кеш файлов
	 * @return int count files
	 */
	public static function clean_cache_files()
	{
		$cache = Front::instance()->_cache_path;
		if (!$cache)
		{
			return 0;
		}

		$i = 0;
		$path = $cache["path"];
		$opendir = opendir($path);

		// clean css/js files
		while ($file = readdir($opendir))
		{
			if ($file === "." OR $file === "..")
			{
				continue;
			}

			if (preg_match("/\.(css|js)$/", $file))
			{
				unlink($path . $file);
				$i++;
			}
		}

		return $i;
	}

	/**
	 * Custom Meta Tags
	 * @param string $tag tag type (meta|link)
	 * @param array $attrs attributes
	 * @return this
	 */
	public static function add_custom_tag($tag,array $attrs = array())
	{
		if (!isset(Front::instance()->_custom_tags[$tag]))
		{
			Front::instance()->_custom_tags[$tag] = array();
		}

		Front::instance()->_custom_tags[$tag][] = $attrs;
		return Front::instance();
	}

	/**
	 * Получить все кастомные теги
	 * @return string
	 */
	public static function get_custom_tags()
	{
		$tags_html = "";
		foreach (Front::instance()->_custom_tags as $tag => $data)
		{
			foreach ($data as $attrs)
			{
				$tags_html .= "\n<{$tag}".HTML::attributes($attrs)."/>";
			}
		}
		return $tags_html;
	}

	/**
	 * Set page title
	 * 
	 * @param string $title title, example "Main", Contacts"
	 * @return this
	 */
	public static function add_title()
	{
		$args = func_get_args();
		foreach ($args as $arg)
		{
			Front::instance()->_title[] = $arg;
		}

		return Front::instance();
	}

	/**
	 * Get page titles
	 * 
	 * @param string $separator separator between titles
	 * @param bolean $reverse array reverse
	 * 
	 * @return string
	 */
	public static function get_title($separator = ' - ', $reverse = true)
	{
		array_walk(Front::instance()->_title, function(&$value)
		{
			$value = trim(strip_tags($value));
		});

		$reverse = (bool)$reverse;
		$string_title = implode($separator,
					($reverse === true
									? array_reverse(Front::instance()->_title)
									: Front::instance()->_title));

		return HTML::chars($string_title);
	}

	/**
	 * Get last title
	 * @return string
	 */
	public static function get_title_last()
	{
		return HTML::chars(trim(strip_tags(array_pop(Front::instance()->_title))));
	}

	/**
	 * Set description in this page
	 * 
	 * @param string $description description
	 */
	public static function add_description()
	{
		$args = func_get_args();
		foreach ($args as $arg)
			if (!empty($arg))
				Front::instance()->_description[] = $arg;

		return Front::instance();
	}

	/**
	 * Get page description
	 * 
	 * @param boolean $take_the_name
	 * @return string
	 */
	public static function get_description($take_the_name = false, $reverse = true)
	{

		array_walk(Front::instance()->_description, function(&$value)
		{
			$value = trim(strip_tags($value));
		});

		$description = Front::instance()->_description;

		if ($reverse === true)
		{
			$description = array_reverse($description);
		}

		$description = implode('. ', $description);
		if ($take_the_name === true)
		{
			$description .= " " . Front::instance()->get_title('. ') . " ";
		}

		$description = mb_substr($description, 0, 250);
		$description = preg_replace("/\s/s", " ", $description);
		$description = HTML::chars(trim($description));

		return $description;
	}

	/**
	 * Set keywords in this page
	 * 
	 * @param string $keywords keywords, example: "Tag1", "Tag2"
	 * @return this
	 */
	public static function add_keywords()
	{
		$args = func_get_args();
		foreach ($args as $arg)
			if (is_array($arg))
				call_user_func_array(array(Front::instance(), "add_keywords"), $arg);
			else
				Front::instance()->_keywords[] = $arg;

		return Front::instance();
	}

	/**
	 * Get page keywords
	 * 
	 * @param bool $take_the_name
	 */
	public static function get_keywords($take_the_name = false) {

		array_walk(Front::instance()->_keywords, function(&$value)
		{
			$value = trim(strip_tags($value));
		});

		$_keywords = Front::instance()->_keywords;
		if ($take_the_name)
		{
			$_keywords[] = Front::instance()->get_title_last();
		}

		$temp_data = implode(', ', $_keywords);

		$clean_data = mb_convert_encoding($temp_data, "UTF-8");
		$clean_data = mb_strtolower($clean_data);
		$clean_data = preg_replace("/[^а-яёa-z0-9,]/u", " ", $clean_data);
		$clean_data = preg_replace("/(\s+)/", " ", $clean_data);

		# разбираем строку на слова
		$array_keywords = explode(",", $clean_data);
		array_walk($array_keywords, function(&$value)
		{
			$value = trim($value);
		});

		# собираем часто повторяющиеся слова
		$frequent_keywords = array();

		foreach ($array_keywords as $keyword) {
			if (isset($frequent_keywords[$keyword])) {
				$frequent_keywords[$keyword]++;
				continue;
			}

			$frequent_keywords[$keyword] = 1;
		}

		arsort($frequent_keywords);

		if (count($frequent_keywords)>20)
			$min_length = 6;
		else if (count($frequent_keywords)>15)
			$min_length = 5;
		else if (count($frequent_keywords)>10)
			$min_length = 4;
		else
			$min_length = 3;

		$new_array_keywords = array();
		foreach ($frequent_keywords as $keyword => $iter) {
			$length = mb_strlen($keyword);

			if ($length >= $min_length and !isset($new_array_keywords[$keyword]))
				$new_array_keywords[$keyword] = 1 + $iter;
			else if ($length >= $min_length)
				$new_array_keywords[$keyword]++;
		}

		arsort($new_array_keywords);

		$ready_keywords = array();
		foreach ($new_array_keywords as $keyword => $count) {
			$ready_keywords[] = trim($keyword);
			if (count($ready_keywords) > 16)
				break;
		}

		$keywords = implode(", ", $ready_keywords);
		$keywords = HTML::chars($keywords);
		return $keywords;
	}

	/**
	 * Add less
	 * 
	 * @param string $filepath
	 * @param array $params
	 */
	public static function add_less($filepath, array $params = array())
	{
		$filepath = trim($filepath, "/");
		if (preg_match('/\/\//', $filepath))
		{
			Front::instance()->_less[] = array(
				'file' => $filepath,
				'params' => array_merge(array('external'=>true), $params)
			);

			return Front::instance();
		}

		# replace .less ext
		$filepath = preg_replace("/\.less$/i", "", $filepath);

		# if no path (one word), set less/ dir
		if (!preg_match("/\//", $filepath))
		{
			$filepath = "less/".$filepath;
		}

		Front::instance()->_less[] = array(
			'file' => $filepath . ".less",
			'params' => array_merge(array('external'=>false), $params)
		);

		return Front::instance();
	}

	/**
	 * Set style links
	 * 
	 * @param string $filepath link on style
	 */
	public static function add_style($filepath, array $params = array())
	{
		$filepath = trim($filepath, "/");

		# if external link
		if (preg_match('/\/\//', $filepath))
		{
			Front::instance()->_style[] = array(
				'file' => $filepath,
				'params' => array_merge(array('external'=>true), $params)
			);

			return Front::instance();
		}

		# replace .css ext
		$filepath = preg_replace("/\.css$/i", "", $filepath);

		# if no path (one word), set css/ dir
		if (!preg_match("/\//", $filepath))
		{
			$filepath = "css/".$filepath;
		}

		Front::instance()->_style[] = array(
			'file' => $filepath . ".css",
			'params' => array_merge(array('external'=>false), $params)
		);

		return Front::instance();
	}

	/**
	 * Get styles, ready links
	 * 
	 * @return string
	 */
	public static function get_styles($merge = false, $compress = false)
	{
		# проходимся по массиву файлов и убираем те, которые найти не удалось
		Front::instance()->construct_data(Front::instance()->_style);
		Front::instance()->construct_data(Front::instance()->_less);

		$html = "";

		$merge = (bool) $merge;
		$compress = (bool) $compress;

		// кешируем стили
		if ($merge and Front::instance()->_cache_path)
		{
			$cache_dir = Front::instance()->_cache_path["path"];
			$cache_www = Front::instance()->_cache_path["www"];
			$cache_filename = sha1("template_css_cache_" . 
					Front::instance()->get_cache_name(Front::instance()->_style)).".css";

			$cache_content = "";

			if (!file_exists($cache_dir.$cache_filename))
			{
				# создаем кеш
				foreach (Front::instance()->_style as $file)
				{
					# нужно ли объединять этот файл с остальными
					if (isset($file['params']['merge']) and (bool)$file['params']['merge'] === false)
					{
						continue;
					}

					# собираем весь css код в один файл
					$file_content = @file_get_contents($file['file']);
					if (!$file_content)
					{
						continue;
					}

					$file_content = preg_replace_callback("/url\((.+)\)/iUmu", function($matches) {
						return "url(".trim(trim($matches[1], "'"), '"').")";
					}, $file_content);

					preg_match_all("/url\((?<urls>.+)\)/iUmu", $file_content, $matches);
					
					if (!empty($matches["urls"]))
					{
						$urls = $matches["urls"];
						array_walk($urls, function(&$_tempvar) {
							if (preg_match("/\/\//", $_tempvar))
							{
								$_tempvar = NULL;
							}
						});

						$urls = array_unique($urls);
						$file_folders = explode("/", $file['file']);
						$protocol = array_shift($file_folders);
						array_shift($file_folders);
						$domain = array_shift($file_folders);
						$www = $protocol . "//" . $domain . "/";
						foreach ($urls as $url)
						{
							$temp_file_folders = $file_folders;
							array_pop($temp_file_folders);

							$new_url = $www . implode("/", $temp_file_folders) . "/" . trim($url, "/");
							$file_content = str_replace("(".$url.")", "(".$new_url.")", $file_content);
						}
					}
					$cache_content .= $file_content;
				}

				# сохраним
				$fp = @fopen($cache_dir . $cache_filename, 'w+');
				if ($fp)
				{
					if ($compress === true)
					{
						// если нужна компрессия, подключаем CSS Tidy и делаем ее
						require_once Kohana::find_file('vendor', 'CSSTidy/class.csstidy');
						$csstidy = new csstidy();
						$csstidy->set_cfg('remove_last_;', TRUE);
						$csstidy->set_cfg('case_properties', 1);
						$csstidy->set_cfg('merge_selectors', 0);
						$csstidy->set_cfg('optimise_shorthands', 1);
						$csstidy->set_cfg('css_level', "CSS2.1");
						$csstidy->load_template("highest_compression");

						$csstidy->parse($cache_content);

						$cache_content = $csstidy->print->plain();
					}
					fwrite($fp, $cache_content);
					fclose($fp);
				}
			}

			# если кеш файл создан, подключаем его
			if (file_exists($cache_dir.$cache_filename))
			{
				$html .= HTML::style($cache_www . $cache_filename)."\n";
				# подключаем все, что не в кеше
				foreach (Front::instance()->_style as $file)
				{
					if (isset($file['params']['merge']) and (bool)$file['params']['merge'] === false)
						$html .= Arr::get($file['params'], 'before') . HTML::style($file['file']) . Arr::get($file['params'], 'after')."\n";
				}
				
				# подключим less файлы
				foreach (Front::instance()->_less as $file)
				{
					$html .= Arr::get($file['params'], 'before') . HTML::style($file['file'], array('rel' => 'stylesheet/less')) . Arr::get($file['params'], 'after') . "\n";
				}

				return $html;
			}
		}

		foreach (Front::instance()->_style as $file)
		{
			$html .= Arr::get($file['params'], 'before') . HTML::style($file['file']) . Arr::get($file['params'], 'after') ."\n";
		}

		# подключим less файлы
		foreach (Front::instance()->_less as $file)
		{
			$html .= Arr::get($file['params'], 'before') . HTML::style($file['file'], array('rel' => 'stylesheet/less')) . Arr::get($file['params'], 'after') . "\n";
		}

		return $html;
	}

	/**
	 * Set script links
	 * 
	 * @param string $filepath link on javascript
	 */
	public static function add_script($filepath, $params = array())
	{
		# if external link
		if (preg_match('/\/\//', $filepath))
		{
			Front::instance()->_script[] = array('file' => $filepath, 'params' => array_merge(array('external'=>true), $params));

			return Front::instance();
		}

		# replace .js ext
		$filepath = preg_replace("/\.js$/i", "", $filepath);

		# if no path ( one word ), set js/ dir
		if (!preg_match("/\//", $filepath))
		{
			$filepath = "js/".$filepath;
		}

		# добавляем в массив
		Front::instance()->_script[] = array('file' => $filepath . ".js", 'params' => array_merge(array('external'=>false), $params));

		return Front::instance();
	}

	/**
	 * Get scripts, ready links
	 * 
	 * @return string
	 */
	public static function get_scripts($merge = false) {

		# проходим по массиву и убираем файлы, которые не удалось найти
		Front::instance()->construct_data(Front::instance()->_script);

		$html = "";

		$merge = (bool) $merge;

		if ($merge and Front::instance()->_cache_path)
		{
			$cache_dir = Front::instance()->_cache_path["path"];
			$cache_www = Front::instance()->_cache_path["www"];
			$cache_filename = sha1("template_js_cache_"
				. Front::instance()->get_cache_name(Front::instance()->_script)).".js";

			$cache_content = "";

			if (!file_exists($cache_dir.$cache_filename))
			{
				# создаем кеш
				foreach (Front::instance()->_script as $file)
				{
					# нужно ли объединять этот файл с остальными
					if (isset($file['params']['merge']) and (bool)$file['params']['merge'] === false)
						continue;

					# собираем весь js код в один файл
					$file_content = @file_get_contents($file['file'] );
					if (!$file_content)
						continue;

					if ($file_content)
						$cache_content .= $file_content .";\n\r";
				}

				# сохраним
				$fp = @fopen($cache_dir.$cache_filename, 'w+');
				if ($fp)
				{
					fwrite($fp, $cache_content);
					fclose($fp);
				}
			}

			# подключаем кеш файл
			if (file_exists($cache_dir.$cache_filename))
			{
				$html .= HTML::script($cache_www.$cache_filename)."\n";

				# подключаем все, что не в кеше
				foreach (Front::instance()->_script as $file)
				{
					if (isset($file['params']['merge']) and (bool)$file['params']['merge'] === false)
						$html .= Arr::get($file['params'], 'before') . HTML::script($file['file']). Arr::get($file['params'], 'after') . "\n";
				}
				return $html;
			}
		}

		foreach (Front::instance()->_script as $file)
		{
			$html .= Arr::get($file['params'], 'before') . HTML::script($file['file']). Arr::get($file['params'], 'after')."\n";
		}

		return $html;
	}

	/**
	 * Set javascript variable
	 * 
	 * @param string $name var name
	 * @param string $value var value
	 * 
	 * @return boolean
	 */
	public static function add_jsvar($name, $value = null)
	{
		if (!isset(Front::instance()->_jsdata['vars'])) {
			Front::instance()->_jsdata['vars'] = array();
		}

		$explode = explode(".", $name);
		$temp_data = Front::instance()->_jsdata['vars'];
		$data = &$temp_data;

		foreach ($explode as $key) {
			if (!isset($data[$key])) {
				$data[$key] = array();
			}
			$data = &$data[$key];
		}

		$data = $value;
		Front::instance()->_jsdata['vars'] = $temp_data;
		return Front::instance();
	}

	/**
	 * Set javascript init funcions
	 * 
	 * @param string $func function name
	 * @return boolean
	 */
	public static function add_jsinit($func)
	{
		Front::instance()->_jsdata['funcs'][] = $func;
		return true;
	}

	/**
	 * Add Lang Text
	 * @param key $key lang default (en)
	 * @param key $value lang current
	 * @return this
	 */
	public static function add_lang($key,$value = null)
	{
		if (is_null($value))
		{
			$value = __($key);
		} else if (is_array($value))
		{
			$value = __($key, $value);
		} else if (!is_string($value))
		{
			$value = NULL;
		}

		Front::instance()->_langs[$key] = $value;
		return Front::instance();
	}

	/**
	 * Remove Lang Text
	 * @param key $key lang default (en)
	 * @return this
	 */
	public static function remove_lang($key)
	{
		if (isset(Front::instance()->_langs[$key]))
		{
			unset(Front::instance()->_langs[$key]);
		}
		return Front::instance();
	}

	/**
	 * Get js data, ready
	 * 
	 * @return string
	 */
	public static function get_jsdata()
	{

		if (!count(Front::instance()->_jsdata))
			return null;
		
		$html = "<script type=\"text/javascript\">\n";
		$vars = array();
		if (!isset(Front::instance()->_jsdata['vars']))
		{
			Front::instance()->_jsdata['vars'] = array();
		}
		
		$js_data = array_merge(Front::instance()->_jsdata['vars'], 
			array(Front::instance()->_jsvar_langs => Front::instance()->_langs));

		if (!empty($js_data))
		{
			foreach ($js_data as $var => $value)
			{
				$vars[$var] = $value;
			}
			$jsvar = Front::instance()->_jsvar;
			$encode = json_encode($vars);

			$html .= "var {$jsvar} = ".$encode.";\n";
		}

		if (!empty(Front::instance()->_jsdata['funcs']))
		{
			$html .= "if (jQuery !== undefined){jQuery(function(){";
			Front::instance()->_jsdata['funcs'] = array_unique(Front::instance()->_jsdata['funcs']);
			foreach (Front::instance()->_jsdata['funcs'] as $key => $func)
			{
				$html .= "$func;\n";
			}
			$html .= "});}";
		}

		return $html .= "</script>";
	}

	/**
	 * Генерирует имя кеш файла, исходя от переданных путей
	 */
	protected static function get_cache_name(array $data = array())
	{
		$hash = array();
		foreach ($data as $file)
		{
			if (!isset($file["params"]["merge"]) OR $file["params"]["merge"])
			{
				$hash[] = $file["file"];
			}
		}
		return md5(implode("|", $hash));
	}

	/**
	 * Ищет по всем путям файлы, возвращает www адрес найденного файла
	 */
	protected function find_file($filepath)
	{
		foreach (Front::instance()->_paths as $data)
		{
			if (file_exists($data["path"] . $filepath))
			{
				return $data["www"] . $filepath;
			}
		}
		return NULL;
	}

	/**
	 * Собираем данные, уникализируем файлы (2 одинаковых ненужно), ищем в библиотеках нужные файлы
	 */
	protected function construct_data(&$data=array())
	{
		$unique = array();
		foreach ($data as $key => $filedata)
		{
			if (in_array($filedata['file'], $unique))
			{
				unset($data[$key]);
				continue;
			}

			$unique[] = $filedata['file'];

			if ($filedata['params']['external'])
			{
				continue;
			}

			if (!$full = Front::instance()->find_file($filedata['file']))
			{
				unset($data[$key]);
			} else
			{
				$data[$key]['file'] = $full;
			}
		}
	}

	/**
	 * Получаем все в html
	 */
	public static function get_html()
	{
		return '<title>'.Front::get_title(" / ").'</title>
		<meta name="description" content="'.Front::get_description().'">
		<meta name="keywords" content="'.Front::get_keywords().'">'
		.	Front::get_custom_tags() . (Front::get_styles((bool)Front::instance()->_config["cssmerge"], (bool)Front::instance()->_config["csscompress"])
								. Front::get_scripts((bool)Front::instance()->_config["jsmerge"])
								. Front::get_jsdata());
	}
}