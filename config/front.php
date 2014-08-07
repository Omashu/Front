<?php defined('SYSPATH') or die('No direct script access.');

$config = [];

// set on init
$config["apply"]["meta"]["title"][] = "Site.com";
$config["apply"]["meta"]["description"][] = "Qwerty";
$config["apply"]["meta"]["keywords"][] = "Qwerty";

// examples:
$config["apply"]["asset"]["js"][] = ["https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"];
$config["apply"]["asset"]["js"][] = ["http://static.aninova.ru/libs/js/html5.js", ['merge' => false, 'before' => '<!--[if lt IE 9]>', 'after' => '<![endif]-->']];
$config["apply"]["asset"]["css"][] = ["http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css", ["merge" => FALSE]];

// $config["apply"]["variable"]["lang"][] = array("My site");
// $config["apply"]["variable"]["jsvar"][] = array("user", array("id" => 123, "username" => "Sanji"));
// $config["apply"]["variable"]["jsvar"][] = array("user.tm", time());

// configure meta
$config["configure"]["meta"]["title"]["separator"] = " / ";
$config["configure"]["meta"]["title"]["reverse"] = TRUE;

// configure asset
// $config["configure"]["asset"]["cache_paths"] = array(
// 	"path" => DOCROOT . "assets/cache",
// 	"http" => "http://site.com/assets/cache"
// );

// paths to find your js/css/less
// $config["configure"]["asset"]["paths"] = array(
// 	array(
// 		"path" => DOCROOT . "assets/libs",
// 		"http" => "http://site.com/assets/libs",
// 	),
// 	array(
// 		"path" => DOCROOT . "assets_old/libs",
// 		"http" => "http://site.com/assets_old/libs",
// 	),
// );

// configure lang
// $config["configure"]["meta"]["global"] = "__myVarName";

return $config;