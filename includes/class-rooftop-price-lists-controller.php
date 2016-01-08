<?php

class Rooftop_Price_Lists_Controller extends Rooftop_Controller {
    public function event_price_list_links_filter( $links, $post ) {
        $prefix = "rooftop-events/v2";
        $base = "$prefix/price_lists";

        $links['prices'] = array(
            'href' => rest_url( trailingslashit( $base ) . $post->ID . '/prices' ),
            'embeddable' => true
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
        register_rest_route( 'rooftop-events/v2', '/price_lists', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_lists' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_price_list' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
        ) );

        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_list' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_price_list' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_price_list' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
        ) );
    }

    public function get_price_lists( $request ) {
        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        if( $context === 'embed' ) {
            $price_list_id = get_post_meta( $request['parent'], 'price_list_id', true );

            return $this->get_price_list( array('id' => $price_list_id, 'context' => 'embed' ) );
        }else if( $context==="view" && $request['parent'] && $request['type'] ) {
            $price_list_id = null;

            if( $request['type'] === 'price' ) {
                $price_post = get_post( $request['parent'] );
                $price_list_id = get_post_meta( $price_post->ID, 'price_list_id', true );
            }elseif( $request['type'] === 'instance' ) {
                $event_instance = get_post( $request['parent'] );
                $price_list_id = get_post_meta( $event_instance->ID, 'price_list_id', true );
            }

            if( $price_list_id ) {
                return $this->get_item( array('id' => $price_list_id, 'context' => 'view' ) );
            }
        }

        return $this->get_items( $request );
    }
    public function get_price_list( $request ) {
        return $this->get_item( $request );
    }
    public function create_price_list( $request ) {
        return $this->create_item( $request );
    }
    public function update_price_list( $request ) {
        return $this->update_item( $request );
    }
    public function delete_price_list( $request ) {
        return $this->delete_item( $request );
    }
}
