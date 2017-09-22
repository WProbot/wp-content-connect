<?php

namespace TenUp\ContentConnect\Tests;

use TenUp\ContentConnect\Registry;
use TenUp\ContentConnect\Relationships\PostToPost;
use TenUp\ContentConnect\Relationships\PostToUser;

class RegistryTest extends ContentConnectTestCase {

	public function setUp() {
		parent::setUp(); // TODO: Change the autogenerated stub
	}

	public function test_relationship_doesnt_exist() {
		$registry = new Registry();

		$this->assertFalse( $registry->post_to_post_relationship_exists( 'post', 'post', 'basic' ) );
		$this->assertFalse( $registry->post_to_user_relationship_exists( 'post', 'owner' ) );
	}

	public function test_relationship_can_be_added() {
		$registry = new Registry();

		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'basic' ) );
		$this->assertInstanceOf( PostToUser::class, $registry->define_post_to_user( 'post', 'owner' ) );
	}

	public function test_doesnt_add_duplicate_post_to_post_relationship() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', 'post', 'basic' );
		$registry->define_post_to_post( 'post', 'post', 'basic' );
	}

	public function test_doesnt_add_duplicate_post_to_user_relationship() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_user( 'post', 'owner' );
		$registry->define_post_to_user( 'post', 'owner' );
	}

	public function test_can_define_different_types_for_same_cpts() {
		$registry = new Registry();

		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'type1' ) );
		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'type2' ) );

		$this->assertInstanceOf( PostToUser::class, $registry->define_post_to_user( 'post', 'owner' ) );
		$this->assertInstanceOf( PostToUser::class, $registry->define_post_to_user( 'post', 'contrib' ) );
	}

	public function test_flipped_order_is_still_duplicate() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', 'car', 'basic' );
		$registry->define_post_to_post( 'car', 'post', 'basic' );
	}

	public function test_retreival_of_post_to_post_relationships() {
		$registry = new Registry();

		// Add all the relationship types so we know we aren't just lucky in the return values
		$pp = $registry->define_post_to_post( 'post', 'post', 'basic' );
		$pc = $registry->define_post_to_post( 'post', 'car', 'basic' );
		$pt = $registry->define_post_to_post( 'post', 'tire', 'basic' );
		$ct = $registry->define_post_to_post( 'car', 'tire', 'basic' );
		$cc = $registry->define_post_to_post( 'car', 'car', 'basic' );
		$tt = $registry->define_post_to_post( 'tire', 'tire', 'basic' );

		$tt2 = new PostToPost( 'tire', 'tire', 'basic' );

		// Verify that two separate objects are NOT the same (sanity check)
		$this->assertNotSame( $tt, $tt2 );

		$this->assertSame( $pp, $registry->get_post_to_post_relationship( 'post', 'post', 'basic' ) );

		// Check that it doesn't matter the order of args
		$this->assertSame( $pc, $registry->get_post_to_post_relationship( 'post', 'car', 'basic' ) );
		$this->assertSame( $pc, $registry->get_post_to_post_relationship( 'car', 'post', 'basic' ) );

		// Check that calling inverse args returns the same as well (it should, based on above two tests)
		$this->assertSame( $registry->get_post_to_post_relationship( 'post', 'car', 'basic' ), $registry->get_post_to_post_relationship( 'car', 'post', 'basic' ) );
	}

	public function test_retreival_of_post_to_user_relationships() {
		$registry = new Registry();

		$po = $registry->define_post_to_user( 'post', 'owner' );
		$pc = $registry->define_post_to_user( 'post', 'contrib' );

		$pc2 = new PostToUser( 'post', 'contrib' );

		// verify that two separate objects are NOT the same (sanity check)
		$this->assertNotSame( $pc, $pc2 );

		$this->assertSame( $po, $registry->get_post_to_user_relationship( 'post', 'owner' ) );
		$this->assertSame( $pc, $registry->get_post_to_user_relationship( 'post', 'contrib' ) );
	}

	public function test_retreival_of_unique_relationship_names_on_same_cpt() {
		$registry = new Registry();

		$pp1 = $registry->define_post_to_post( 'post', 'post', 'type1' );
		$pp2 = $registry->define_post_to_post( 'post', 'post', 'type2' );

		$this->assertSame( $pp1, $registry->get_post_to_post_relationship( 'post', 'post', 'type1' ) );
		$this->assertSame( $pp2, $registry->get_post_to_post_relationship( 'post', 'post', 'type2' ) );
	}

}
