<?php

namespace Derweili\UserDepartments;
use \WP_User;

defined( 'ABSPATH' ) or die();

class User {

	private $user;

	private $department_ids = [];
	private $departments = [];

		
	/**
	 * __construct
	 *
	 * @param  int|WP_User $user 
	 * @return void
	 */
	function __construct( $user ) {
		if( is_integer( $user ) ) {
			$this->user = get_user_by( "id", $user );
		} elseif ( $user instanceof WP_User) {
			$this->user = $user;
		} else {
			return;
		}

		// var_dump($user);

		$this->load_department();
	}

	function load_department() {
		$department_ids = get_user_meta( $this->user->id, Department::get_user_meta_key(), false );

		$departments = array_map( function( $department_id ) {
			return new Department( intval( $department_id ) );
		}, $department_ids);

		$this->department_ids = $department_ids;
		$this->departments = $departments;
	}

	function has_department( $id = null ) {
		if ( 0 === count( $this->departments ) ) {
			return false;
		}

		if( null !== $id ) {
			return in_array( $id, $this->department_ids );
		}

		return true;
	}

	function get_departments() {
		return $this->departments;
	}

	function save_departments( $department_ids ) {
		delete_user_meta( $this->user->id, Department::get_user_meta_key() );

		foreach ($department_ids as $department_id) {
			add_user_meta( $this->user->id, Department::get_user_meta_key(), $department_id, false );
		}
	}

}