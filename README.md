Kohana 3.3 Front Module
=====================

Install:
-------------------

Clone module in your directory:

	`cd kohana/modules`
	`git clone https://github.com/Omashu/Front.git front`

Require module in bootstrap.php

	`'front' => MODPATH.'front',`

Base use:
-------------------

# Adding title, description or keywords in your page:
	Front::meta()
		->title("Users", "Page 1")
		->description("User list")
		->keywords("User list", "Key");

# Require js/css/less:
	Front::asset()
		->js('path/to/js', array $params)
		->css('path/to/css', array $params)
		->less('path/to/less', array $params)

## array $params
	* `before` => before insert your value, example: `<!--[if lt IE 9]>`
	* `after` => after insert your value, example: `<![endif]-->`
	* `merge` => if cache enable, merge this file with the other files?
	* `priority` => file priority
	* `external` => force, mark this file is external

# Output langs and other values in JS variable:
	Front::variable()
		->lang("My site", "Meine Website")
		->lang(":q site", array(":q" => "My"))
		->jsvar("user", array("id" => 1, "first_name" => "Ilya"))
		->jsvar("user.username", "Sanji")