<?php
/**
 * Class EventMetadataTest
 *
 * @package Rooftop_Events
 */

/**
 * Sample test case.
 */
class EventsTest extends WP_UnitTestCase {

    public function setUp() {
        parent::setUp();

        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;

        wp_set_current_user( 1 ); // run requests as the admin user
        do_action( 'rest_api_init' );

        $this->event = $this->insert_event_data();
    }

    function insert_event_data() {
        $event_args = array(
            'post_type' => 'event',
            'post_title' => 'test event',
            'post_status' => 'publish'
        );

        $event_instance_args = array(
            'post_type' => 'event_instance',
            'post_title' => 'test event instance',
            'post_status' => 'publish'
        );

        $event_id     = wp_insert_post($event_args);
        $instance1_id = wp_insert_post($event_instance_args);
        $instance2_id = wp_insert_post($event_instance_args);

        $event = get_post($event_id);
        $instance1   = get_post($instance1_id);
        $instance2   = get_post($instance2_id);

        // associate the instance with that event
        add_post_meta( $instance1->ID, 'event_id', $event->ID );
        add_post_meta( $instance2->ID, 'event_id', $event->ID );

        update_post_meta( $instance1->ID, 'event_instance_meta', array(
            'availability' => array(
                'starts_at' => '',
                'stops_at'  => '',
                'start_selling_at' => '',
                'stop_selling_at'  => '',
                'seats_capacity'   => 100,
                'seats_available'  => 100
            )
        ) );

        update_post_meta( $instance2->ID, 'event_instance_meta', array(
            'availability' => array(
                'starts_at' => '',
                'stops_at'  => '',
                'start_selling_at' => '',
                'stop_selling_at'  => '',
                'seats_capacity'   => 200,
                'seats_available'  => 200
            )
        ) );

        do_action( 'rooftop_update_event_metadata', $event->ID );

        return $event;
    }

	function test_event_instances_are_added() {
        $request  = new WP_REST_Request( 'POST', '/rooftop-events/v2/events/'.$this->event->ID.'/instances');

        $request->set_param('title', 'example post');
        $request->set_param('status', 'publish');

        $current_instances_count = count( $this->get_event_instances( $this->event->ID ) );

        $this->server->dispatch($request);
        $updated_instances_count = count( $this->get_event_instances( $this->event->ID ) );

        $this->assertTrue( $current_instances_count+1 == $updated_instances_count );
    }

	function test_event_has_availability_metadata() {
        // get the event via the rest api
        $request  = new WP_REST_Request( 'GET', '/rooftop-events/v2/events/'.$this->event->ID);

        $response = $this->server->dispatch( $request );
        $this->assertTrue( array_key_exists( 'event_instance_availabilities', $response->data ) );
	}

	function test_event_has_availability_count() {
        $request  = new WP_REST_Request( 'GET', '/rooftop-events/v2/events/'.$this->event->ID);
        $response = $this->server->dispatch( $request );

        $available = array_sum( array_map( function( $data ) {return $data['seats_available'];}, $response->data['event_instance_availabilities'][0] ) );
        $this->assertEquals( 300, $available );
    }

    function test_event_availability_decreases_when_an_instance_is_trashed() {
        $instances = $this->get_event_instances( $this->event->ID, array( 'publish' ) );
        $instances_metadata = $this->get_event_instances_metadata( $instances );

        // get the current availability with all published instances...
        $current_availability = array_sum( array_map( function( $data ) {return $data['availability']['seats_available'];}, $instances_metadata ) );

        $instance = $instances[0];
        $instance_meta = get_post_meta( $instance->ID, 'event_instance_meta', true );

        wp_trash_post( $instance->ID );

        $decrement = $instance_meta['availability']['seats_available'];
        $updated_instances = $this->get_event_instances( $this->event->ID, array( 'publish' ) );
        $updated_instances_metadata = $this->get_event_instances_metadata( $updated_instances );
        $updated_availability = array_sum( array_map( function( $data ) {return $data['availability']['seats_available'];}, $updated_instances_metadata ) );

        $this->assertTrue( $current_availability - $decrement == $updated_availability );
    }

    function test_event_availability_increases_when_an_instance_is_restored_from_the_trash() {
	    $instances = $this->get_event_instances( $this->event->ID );

	    $trashed_instance = $instances[0];
        $trashed_instance_meta = get_post_meta( $trashed_instance->ID, 'event_instance_meta', true );
        $increment = $trashed_instance_meta['availability']['seats_available'];

	    wp_trash_post( $trashed_instance->ID );

        $instances = $this->get_event_instances( $this->event->ID );
        $partial_instances_metadata = $this->get_event_instances_metadata( $instances );
        $partial_availability = array_sum( array_map( function( $data ) {return $data['availability']['seats_available'];}, $partial_instances_metadata ) );

        wp_untrash_post( $trashed_instance->ID );

        $instances = $this->get_event_instances( $this->event->ID );
        $updated_instances_metadata = $this->get_event_instances_metadata( $instances );
        $updated_availability = array_sum( array_map( function( $data ) {return $data['availability']['seats_available'];}, $updated_instances_metadata ) );

        $this->assertTrue( $partial_availability + $increment == $updated_availability );
    }








    function get_event_instances( $event_id, $status = array( 'publish ')) {
        $instances = get_posts(array(
                'post_type'  => 'event_instance',
                'meta_key'   => 'event_id',
                'meta_value' => $event_id,
                'post_status' => $status
            )
        );

        return $instances;
    }

    function get_event_instances_metadata( $instances ) {
        $instances_metadata = array();

        foreach( $instances as $instance ) {
            $instances_metadata[] = get_post_meta( $instance->ID, 'event_instance_meta', true );
        }

        return $instances_metadata;
    }

    function get_availability_from_instances( $instances ) {

    }
}
