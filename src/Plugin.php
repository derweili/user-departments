<?php

namespace Derweili\UserDepartments;

defined( 'ABSPATH' ) or die();

class Plugin {
	function __construct() {
		$this->register_hooks();
	}

	function register_hooks() {
		Department::register();
	}

	public static function register_activation_hooks() {
		Department::register_admin_capabilities();
	}
}