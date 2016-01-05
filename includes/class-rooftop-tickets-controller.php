<?php

class WP_REST_Tickets_Controller extends Rooftop_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        $base = $this->get_post_type_base( $this->post_type ) . 's';

        register_rest_route( 'rooftop-events/v2', '/ticket_types', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_ticket_types' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/ticket_types/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_ticket_type' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item_ticket_type' ),
                'permission_callback' => array( $this, 'get_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_item_ticket_type' ),
                'permission_callback' => array( $this, 'get_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
    }

    public function get_ticket_types( $request ) {
        $this->post_type = 'event_ticket_type';

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        if( $context == 'embed' ) {
            $ticket_type_id = get_post_meta( $request['parent'], 'ticket_type_id', true );
            return $this->get_item( array( 'id' => $ticket_type_id, 'context' => 'embed' ) );
        }else {
            return $this->get_items( $request );
        }
    }

    public function get_ticket_type( $request ) {
        $this->post_type = 'event_ticket_type';
        return $this->get_item( $request );
    }

    public function update_ticket_type( $request ) {
        $this->post_type = 'event_ticket_type';
        return $this->update_item( $request );
    }

    public function delete_ticket_type( $request ) {
        $this->post_type = 'event_ticket_type';
        return $this->delete_item( $request );
    }

}
