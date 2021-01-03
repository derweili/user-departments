<?php
/**
 * Plugin Name:     User Departments
 * Plugin URI:      https://github.com/derweili/user-departments
 * Description:     Departments for WordPress Posts and Users
 * Author:          derweili <jw@derweili.de>
 * Author URI:      https://derweili.de
 * Text Domain:     user-departments
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         User_Departments
 */

// Your code starts here.
namespace Derweili\UserDepartments;

defined( 'ABSPATH' ) or die();

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ){
	require __DIR__ . '/vendor/autoload.php';
}

new Plugin();

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'register_activation_hooks' ) );