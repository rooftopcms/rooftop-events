<?php

class Rooftop_Prices_Controller extends Rooftop_Controller {
    function __construct( $post_type ) {
        parent::__construct( $post_type );

        // add the event_id metadata to newly created event instance posts
        add_action( "rooftop_".$this->post_type."_rest_insert_post", function( $prepared_post, $request, $success ){
            update_post_meta( $prepared_post->ID, 'price_list_id', $request['price_list_id'] );
            update_post_meta( $prepared_post->ID, 'ticket_type_id', $request['event_price_meta']['ticket_type_id'] );
            update_post_meta( $prepared_post->ID, 'price_band_id',  $request['event_price_meta']['price_band_id'] );

            return $prepared_post;
        }, 10, 3);

        add_action( "rest_prepare_".$this->post_type, function( $response, $post, $request ) {
            $event_price_meta = get_post_meta( $post->ID, 'event_price_meta', true );
            $response->data['event_price_meta'] = $event_price_meta;

            return $response;
        }, 10, 3);
    }

    public function event_price_links_filter( $links, $post ) {
        $prefix = "rooftop-events/v2";
        $price_list_id = get_post_meta( $post->ID, 'price_list_id', true );

        $links['ticket_type'] = array(
            'href' => rest_url( trailingslashit( $prefix ) . 'ticket_types?parent=' . $post->ID ),
            'embeddable' => true
        );

        $links['price_band'] = array(
            'href' => rest_url( trailingslashit( $prefix ) . 'price_bands?parent=' . $post->ID ),
            'embeddable' => true
        );

        $links['price_list'] = array(
            'href' => rest_url( trailingslashit( $prefix ) . 'price_lists/' . $price_list_id ),
            'embeddable' => true
        );

        $links['self'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'price_lists/' . trailingslashit( $price_list_id ) . 'prices/' . $post->ID ),
        );
        $links['collection'] = array(
            'href'   => rest_url( trailingslashit( $prefix ) . 'price_lists/' . trailingslashit( $price_list_id ) . 'prices' ),
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

        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<price_list_id>[\d]+)/prices', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_prices' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_price' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
        ) );

        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<price_list_id>[\d]+)/prices/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_price' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_price' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
        ) );
    }

    public function get_prices( $request ) {
        $this->post_type = 'event_price';

        add_filter( "rest_{$this->post_type}_query", function( $args, $request ) {
            if( $args['post_type'] === 'event_price' ) {
                $args['meta_key']   = 'price_list_id';
                $args['meta_value'] = $request['price_list_id'];
            }

            return $args;
        }, 10, 2 );

        return $this->get_items( $request );
    }
    public function get_price( $request ) {
        $this->post_type = 'event_price';

        return $this->get_item( $request );
    }
    public function create_price( $request ) {
        $required_keys = array( 'ticket_price', 'price_band_id', 'ticket_type_id' );

        $meta = $request[$this->post_type."_meta"] ? $request[$this->post_type."_meta"] : array();

        if( count( array_intersect_key( array_flip( $required_keys ), $meta ) ) >= count( $required_keys ) ) {
            return $this->create_item( $request );
        }else {
            $error = new WP_REST_Response( array(
                'message'=> 'Insufficient parameters',
                'data'   => array ( 'status' => 422 )
            ) );
            $error->set_status(422);

            return $error;
        }
    }
    public function update_price( $request ) {
        $price_response = $this->update_item( $request );

        return $price_response;
    }
    public function delete_price( $request ) {
        return $this->delete_item( $request );
    }
}
