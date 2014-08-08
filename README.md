Kohana 3.3 Front Module
=====================

Install Using Git:
-------------------

Clone module in your directory:

	cd kohana/modules
	git clone https://github.com/Omashu/Front.git front

Install Using Composer:
-----------------------

	"require": {
		"Omashu/Front"			: "dev-master"
	},
	"repositories":[
		{
			"type":"git",
			"url":"https://github.com/Omashu/Front"
		}
	]

Require module in bootstrap.php

	`'front' => MODPATH.'front',`


Base use:
-------------------

### Adding title, description or keywords in your page:
	Front::meta()
		->title("Users", "Page 1")
		->description("User list")
		->keywords("User list", "Key");

### Require js/css/less:
	Front::asset()
		->js('path/to/js', array $params)
		->css('path/to/css', array $params)
		->less('path/to/less', array $params)

#### array $params
	before => before insert your value, example: <!--[if lt IE 9]>
	after => after insert your value, example: <![endif]-->
	merge => if cache enable, merge this file with the other files?
	priority => file priority
	external => force, mark this file is external

### Output langs and other values in JS variable:
	Front::variable()
		->lang("My site", "Meine Website")
		->lang(":q site", array(":q" => "My"))
		->lang("Hello, :username", "Hi, :username")
		->jsvar("user", array("id" => 1, "first_name" => "Ilya"))
		->jsvar("user.username", "Sanji")

Get lang (client javascript):
	__lang("Hello, :username", {":username":__value("user.username")}); // Hi, Sanji

Get jsvar(client javascript):
	__value("user.first_name") // Ilya

Show data in your html head block:
----------------------------------

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<?= Front::meta() ?>
		<?= Front::asset() ?>
		<?= Front::variable() ?>
	</head>
	<body>
		
	</body>
	</html>

Configurations
-------------------

#### meta

	$config["configure"]["meta"]["title"]["separator"] = string, default ` - `, your separator
	$config["configure"]["meta"]["title"]["reverse"] = bool, default `true`, reverse title stack on output
	$config["configure"]["meta"]["description"]["take_the_title"] = bool, default `true`
	$config["configure"]["meta"]["description"]["reverse"] = bool, default `true`
	$config["configure"]["meta"]["keywords"]["take_the_title"] = bool, default `true`
	$config["configure"]["meta"]["keywords"]["take_the_description"] = bool, default `true`

#### asset

	$config["configure"]["asset"]["cache_paths"] = ['path' => DOCROOT . 'cache', 'http' => 'http://site.com/cache']: if you specify styles and scripts begin to unite into a single file
	$config["configure"]["asset"]["paths"] = [
		[
			"path" => DOCROOT . "assets",
			"http" => "http://site.com/assets"
		],
		[
			"path" => DOCROOT . "libs",
			"http" => "http://site.com/assets"
		]
	]: directory traversal in the search for a local file


On init (apply), executed on the first call
---------------------------------------------------------

#### meta

	$config["apply"]["meta"]["title"][] = "Site";
	$config["apply"]["meta"]["description"][] = "Description";
	$config["apply"]["meta"]["keywords"][] = "Keywords";
	$config["apply"]["meta"]["custom"][] = ["meta", ["attr" => "value"]];

### asset
	$config["apply"]["asset"]["js"][] = ["https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"];
	$config["apply"]["asset"]["js"][] = ["http://static.aninova.ru/libs/js/html5.js", ['merge' => false, 'before' => '<!--[if lt IE 9]>', 'after' => '<![endif]-->']];
	$config["apply"]["asset"]["css"][] = ["http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css", ["merge" => false]];

### variable
	$config["apply"]["variable"]["lang"][] = array("My site");
	$config["apply"]["variable"]["jsvar"][] = array("user.tm", time());
	$config["apply"]["variable"]["jsvar"][] = array("user.id", 1);