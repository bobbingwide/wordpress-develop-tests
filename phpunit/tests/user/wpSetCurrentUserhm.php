<?php 

/**
 * @group user
 */
class Tests_User_WpSetCurrentUser extends WP_UnitTestCase {

	/**
	 * Test that you can set the current user by the name parameter
	 *
	 * Initially we expect the current user to not be set - giving ID=0
	 * Trying to set it to "admin" should return ID=1
	 *
	 * @ticket 20845
	 */
	function test_wp_set_current_user_by_name() {
		$user = wp_get_current_user();
		$this->assertEquals( $user->ID, 0 );
		$admin_user = wp_set_current_user( null, "admin" );
		$this->assertEquals( $admin_user->ID, 1 );
		$this->assertEquals( $admin_user->user_login, "admin" );
	}
	
	/**
	 * Demonstrate that the ID value trumps the name value
	 *
	 */
	function test_wp_set_current_user_id_trumps_name() {
		$user = wp_get_current_user();
		$this->assertEquals( $user->ID, 0 );
		bw_trace2( $user, "user", false );
		$admin_user = wp_set_current_user( "admin", "bobbingwide" );
		
		bw_trace2( $admin_user, "admin_user", false );
		$this->assertEquals( $admin_user->ID, 1 );
		$this->assertEquals( $admin_user->user_login, "admin" );
	}
	
