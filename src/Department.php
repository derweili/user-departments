<?php

namespace Derweili\UserDepartments;

defined( 'ABSPATH' ) or die();

class Department {
	private $term;
	private $id;

	private static $taxonomy = 'derweili-department';

	private static $post_types = [ 'post' ];

	private static $user_meta_key = 'derweili_users_department';
	
	private static $capability_prefix = 'manage_department_';

	public static $capabilities_to_filter = [
		'edit_post',
		'delete_post',
		'publish_post'
	];

	function __construct( $term ) {
		$this->term = get_term( $term );

		$this->id = $this->term->term_id;

	}

	function get_term() {
		return $this->term;
	}

	public static function get_user_meta_key() {
		return self::$user_meta_key;
	}

	public static function register() {
		add_action( 'init', array( get_called_class(), 'register_taxonomy' ), 0 );
		add_action( 'show_user_profile', array( get_called_class(), 'add_user_department_form' ), 0 );
		add_action( 'edit_user_profile', array( get_called_class(), 'add_user_department_form' ), 0 );
		add_action( 'personal_options_update', array( get_called_class(), 'save_users_departments' ), 0 );
		add_action( 'edit_user_profile_update', array( get_called_class(), 'save_users_departments' ), 0 );
		add_filter( 'map_meta_cap', array( get_called_class(), 'filter_post_edit_caps' ), 10, 4 );
		add_filter( 'user_has_cap', array( get_called_class(), 'filter_has_cap' ), 10, 4 );
		add_action( 'save_post', array( get_called_class(), 'auto_assign_terms' ), 10, 3 );
	}

	public static function get_post_types() {
		return apply_filters( 'user_departments_post_types', self::$post_types );
	}

	public static function register_taxonomy() {
		// Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
			'name'              => _x( 'Departments', 'taxonomy general name', 'user-departments' ),
			'singular_name'     => _x( 'Department', 'taxonomy singular name', 'user-departments' ),
			'search_items'      => __( 'Search Departments', 'user-departments' ),
			'all_items'         => __( 'All Departments', 'user-departments' ),
			'parent_item'       => __( 'Parent Department', 'user-departments' ),
			'parent_item_colon' => __( 'Parent Department:', 'user-departments' ),
			'edit_item'         => __( 'Edit Department', 'user-departments' ),
			'update_item'       => __( 'Update Department', 'user-departments' ),
			'add_new_item'      => __( 'Add New Department', 'user-departments' ),
			'new_item_name'     => __( 'New Department Name', 'user-departments' ),
			'menu_name'         => __( 'Department', 'user-departments' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'public'						=> false,
			'show_in_rest'       => true,
			'rewrite'           => array( 'slug' => 'department' ),
			'capabilities' => array(
				// $taxonomy['slug'] = genre;
				'manage_terms'  =>   'manage_' . self::$taxonomy,
				'edit_terms'    =>   'edit_' . self::$taxonomy,
				'delete_terms'  =>   'delete' . self::$taxonomy,
				'assign_terms'  =>   'assign_' . self::$taxonomy,
			),
		);

		register_taxonomy( self::$taxonomy, self::get_post_types(), $args );
	}

	public static function register_admin_capabilities() {
		// die('test');
		$role = get_role( 'administrator' );
 
		// This only works, because it accesses the class instance.
    // would allow the author to edit others' posts for current theme only
    $role->add_cap( 'manage_' . self::$taxonomy, ); 
    $role->add_cap( 'edit_' . self::$taxonomy, ); 
    $role->add_cap( 'delete_' . self::$taxonomy, ); 
    $role->add_cap( 'assign_' . self::$taxonomy, ); 
	}

	public static function get_capabilities_to_filter() {
		return apply_filters('capabilities_to_filter', self::$capabilities_to_filter);
	}

	public static function get_all() {
		$terms = get_terms( self::$taxonomy, array(
			'hide_empty' => false,
		) );

		return array_map( function($term) {
			return new Department( $term->term_id );
		}, $terms);
	}

	public static function get_post_departments( $post ) {
		$terms = get_the_terms( $post, self::$taxonomy );

		if( false === $terms) return [];

		return array_map( function($term) {
			return new Department( $term->term_id );
		}, $terms);
	}

	public static function add_user_department_form( $user ) {
		$user = new User( $user );
		$users_departments = $user->get_departments();
		?>
			<h3><?php _e("Departments", "bluser-departmentsank"); ?></h3>

			<table class="form-table">
			<tr>
					<th><?php _e("Users Departments", "bluser-departmentsank"); ?></th>
					<td>
						<?php
							foreach (self::get_all() as $key => $department) :
								$term = $department->get_term();
						?>
							<label>
								<input
									type="checkbox"
									name="departments[]"
									value="<?php echo $department->id; ?>"
									<?php if( $user->has_department( $department->id ) ) echo "checked"; ?>
									>
								<span class="description"><?php echo $term->name; ?></span>
							</label>
							<br />
						<?php endforeach; ?>
					</td>
			</tr>
			</table>
	<?php 
	}

	public static function save_users_departments( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) { 
			return false; 
		}

		$user = new User( $user_id );

		if( ! isset( $_POST["departments"] ) ) {
			$user->save_departments( [] );
			return;
		};
		
		if( ! is_array( $_POST["departments"] ) ) wp_die('Invalid Departments');
		
		$department_ids = array_map( function( $id ) {
			return intval( $id );
		}, $_POST["departments"]);
		
		$user->save_departments( $department_ids );
	}

	public static function filter_post_edit_caps( $required_caps, $cap, $user_id, $args ) {
		// if( in_array( ))
		if( empty( $args ) ) return $required_caps;
		
		if( ! in_array($cap, self::get_capabilities_to_filter() ) ) return $required_caps;
		// die($cap);
		$post_departments = self::get_post_departments( $args[0] );

		foreach ($post_departments as $department) {
			# code...
			$required_caps[] = self::$capability_prefix . $department->id;
		}

		return $required_caps;
	}


	public static function filter_has_cap( $all_caps, $caps, $args, $user ) {
		if( ! taxonomy_exists( self::$taxonomy ) ) return $all_caps;

		if( ! is_user_logged_in() ) return $all_caps;

		$_user = new User( $user );

		$departments = $_user->get_departments();

		foreach ($departments as $department) {
			# code...
			$department_capability = self::$capability_prefix . $department->id;
			$all_caps[ $department_capability ] = true;
		}

		return $all_caps;
	}

	public static function auto_assign_terms( $post_id, $post, $update ) {
		if( $update ) return;
		if ( wp_is_post_revision( $post_id ) ) {
		 return;
		}


		$user = new User( wp_get_current_user() );
		
		if( ! $user->has_department() ) return;

		$term_ids = array_map(function($department) {
			return $department->id;
		}, $user->get_departments() );

		wp_set_post_terms($post_id, $term_ids, self::$taxonomy);
	}
}