<?php

class Rooftop_Price_Bands_Controller extends Rooftop_Controller {
    public function event_price_band_links_filter( $links, $post ) {
        $prefix = "rooftop-events/v2";

        $links['self'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'price_bands/' . $post->ID ),
        );
        $links['collection'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'price_bands' ),
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
        register_rest_route( 'rooftop-events/v2', '/price_bands', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_bands' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_price_band' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
        ) );

        register_rest_route( 'rooftop-events/v2', '/price_bands/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_band' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_price_band' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_price_band' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
        ) );
    }

    public function get_price_bands( $request ) {
        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        if( $context == 'embed' ) {
            $price_band_id = get_post_meta( $request['parent'], 'price_band_id', true );
            return $this->get_item( array( 'id' => $price_band_id, 'context' => 'embed' ) );
        }else {
            return $this->get_items( $request );
        }
    }
    public function get_price_band( $request ) {
        return $this->get_item( $request );
    }
    public function create_price_band( $request ) {
        return $this->create_item( $request );
    }
    public function update_price_band( $request ) {
        return $this->update_item( $request );
    }
    public function delete_price_band( $request ) {
        return $this->delete_item( $request );
    }
}
