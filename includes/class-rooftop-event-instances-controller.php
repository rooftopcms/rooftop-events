<?php

class Rooftop_Event_Instances_Controller extends Rooftop_Controller {
    function __construct( $post_type ) {
        parent::__construct( $post_type );

        // add the event_id metadata to newly created event instance posts
        add_action( "rooftop_".$this->post_type."_rest_insert_post", function( $prepared_post, $request, $success ){
            update_post_meta( $prepared_post->ID, 'event_id', $request['event_id'] );

            return $prepared_post;
        }, 10, 3);

        add_action( "rest_prepare_".$this->post_type, function( $response, $post, $request ) {
            $availability = get_post_meta( $post->ID, 'availability', false );

            if( $availability && count( $availability ) ) {
                $response->data['availability'] = $availability;
            }

            return $response;
        }, 10, 3);
    }

    public function event_instance_links_filter( $links, $post ) {
        $event_id = get_post_meta( $post->ID, 'event_id', true );

        $prefix = "rooftop-events/v2";
        $base = "$prefix/events/$event_id/instances";

        $links['price_list'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'price_lists?type=instance&parent=' . $post->ID),
            'embeddable' => true
        );

        $links['event'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'events/' . $event_id),
        );

        $links['self'] = array(
            'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
        );
		$links['collection'] = array(
            'href'   => rest_url( $base ),
        );
	    $links['about'] = array(
            'href'   => rest_url( '/wp/v2/types/' . $this->post_type ),
        );

        return $links;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( 'rooftop-events/v2', '/events/(?P<event_id>[\d]+)/instances', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_event_instances' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_event_instance' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            )
        ) );

        register_rest_route( 'rooftop-events/v2', '/events/(?P<event_id>[\d]+)/instances/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_event_instance' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_event_instance' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_event_instance' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            )
        ) );
    }

    public function get_event_instances( $request ) {
        add_filter( 'rest_post_query', function( $args, $request ) {
            if( $args['post_type'] === 'event_instance' ) {
                $args['meta_key']   = 'event_id';
                $args['meta_value'] = $request['event_id'];
            }

            return $args;
        }, 10, 2 );

        return $this->get_items( $request );
    }

    public function get_event_instance( $request ) {
        return $this->get_item( $request );
    }

    public function create_event_instance( $request ) {
        $event_id = $request['event_id'];
        $event_post = get_post( $event_id );

        if( $event_post ) {
            return $this->create_item( $request );
        }else {
            $error = new WP_REST_Response( array(
                'message'=> 'Event not found'
            ) );
            $error->set_status(404);

            return $error;
        }
    }
    public function update_event_instance( $request ) {
        return $this->update_item( $request );
    }
    public function delete_event_instance( $request ) {
        return $this->delete_item( $request );
    }
}
