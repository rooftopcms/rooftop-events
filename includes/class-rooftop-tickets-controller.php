<?php

class Rooftop_Tickets_Controller extends Rooftop_Controller {
    public function event_ticket_type_links_filter( $links, $post ) {
        $prefix = "rooftop-events/v2";

        $links['self'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'ticket_types/' . $post->ID ),
        );
        $links['collection'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'ticket_types' ),
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

        $base = $this->get_post_type_base( $this->post_type ) . 's';

        register_rest_route( 'rooftop-events/v2', '/ticket_types', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_ticket_types' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'create_ticket_type' ),
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
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_item_ticket_type' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
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
        }else if( $context == 'view' && $request['parent'] ) {
            $price_post = get_post( $request['parent'] );
            $ticket_type_id = get_post_meta( $price_post->ID, 'ticket_type_id', true);

            if( $ticket_type_id ) {
                return $this->get_item( array( 'id' => $ticket_type_id, 'context' => 'view' ) );
            }
        }else {
            return $this->get_items( $request );
        }
    }

    public function get_ticket_type( $request ) {
        $this->post_type = 'event_ticket_type';
        return $this->get_item( $request );
    }

    public function create_ticket_type( $request ) {
        $this->post_type = 'event_ticket_type';
        return $this->create_item( $request );
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
