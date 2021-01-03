<?php
/**
 * Plugin Name:     User Departments
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
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