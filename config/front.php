<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
	'cssmerge' => false,
	'csscompress' => false,
	'jsmerge' => false,

	// ваши пути для поиска css/js, можно задать тут как по умолчанию, либо в своем приложении
	'path_static' => array(
		// example: array('path'=>DOCROOT.'assets','www'=>URL::base(TRUE).'assets')
		// example: array('path'=>DOCROOT1.'assets','www'=>'http://mydomain.net/assets')
	),

	// путь для хранения кеш файлов css/js
	'path_cache' => array("path"=>STATIC_PATH."cache","www"=>STATIC_URL."cache"),

	// seo data, задаем тут дефолтные данные, в контроллерах добавляем к каждой страничке
	"title" => array(/*"Title 1", "Title 2"*/),
	"description" => array(),
	"keywords" => array(),
	"custom_tags" => array(
		// array(Front::TAG_META, array("generator" => "", "attr1" => ""))
	),

	// подключаем по умолчанию
	"style" => array(
		array("style", array("merge" => false)),
	),
	"less" => array(
		array("less", array("merge" => false)),
	),
	"script" => array(
		array("jquery.min", array("merge" => false)),
	),
	"jsdata_vars" => array(
		array("varName", "value"),
	),
	"jsdata_funcs" => array(
		// "(function(){alert(1)})();", // start on DOM loaded, use jquery
	),
	"langs" => array(
		// array("Site", "Standort")
		// array("Site"), // выполним __("Site")
		// array(":q Site", array(":q"=>"My")) // выполним __(":q Site", array(":q"=>"My"))
	),

	// global js vars
	"jsvar" => "__frontVars",
	"jsvar_langs" => "__langs",
);