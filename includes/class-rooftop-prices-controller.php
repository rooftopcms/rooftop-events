<?php

class WP_REST_Prices_Controller extends Rooftop_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    public function event_price_list_links_filter( $links, $post ) {
        $links['prices'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'price_lists/' . $post->ID . '/prices' ),
            'embeddable' => true
        );

        return $links;
    }

    public function event_price_links_filter( $links, $post ) {
        $links['ticket_type'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'ticket_types?parent=' . $post->ID ),
            'embeddable' => true
        );

        $links['price_band'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'price_bands?parent=' . $post->ID ),
            'embeddable' => true
        );

        return $links;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        $base = $this->get_post_type_base( $this->post_type ) . 's';

        register_rest_route( 'rooftop-events/v2', '/price_lists', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_lists' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_price_list' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
        ) );

        $this->add_link_filters( 'event_price_list' );

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

        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<price_list_id>[\d]+)/prices', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_prices' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_price' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
        ) );
        $this->add_link_filters( 'event_price' );

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

        register_rest_route( 'rooftop-events/v2', '/price_bands', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_bands' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        $this->add_link_filters( 'event_price_band' );

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

    public function get_price_lists( $request ) {
        $this->post_type = 'event_price_list';

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        if( $context === 'embed' ) {
            $price_list_id = get_post_meta( $request['parent'], 'price_list_id', true );

            return $this->get_price_list( array('id' => $price_list_id, 'context' => 'embed' ) );
        }

        return $this->get_items( $request );
    }
    public function get_price_list( $request ) {
        $this->post_type = 'event_price_list';
        return $this->get_item( $request );
    }
    public function create_price_list( $request ) {
        $this->post_type = 'event_price_list';
        return $this->create_item( $request );
    }
    public function update_price_list( $request ) {
        $this->post_type = 'event_price_list';
        return $this->update_item( $request );
    }
    public function delete_price_list( $request ) {
        $this->post_type = 'event_price_list';
        return $this->delete_item( $request );
    }

    public function get_prices( $request ) {
        $this->post_type = 'event_price';

        add_filter( 'rest_post_query', function( $args, $request ) {
            if( $args['post_type'] === 'event_price' ) {
                $args['meta_key']   = 'price_list_id';
                $args['meta_value'] = $request['price_list_id'];
            }

            return $args;
        }, 10, 2 );

        add_filter('rest_prepare_event_price', function( $response, $post, $request ) {
            $ticket_price = get_post_meta( $post->ID, 'ticket_price', true );
            $response->data['ticket_price'] = apply_filters( 'rooftop_format_money', $ticket_price );
            return $response;
        }, 10, 3);

        return $this->get_items( $request );
    }
    public function get_price( $request ) {
        $this->post_type = 'event_price';

        add_filter('rest_prepare_event_price', function( $response, $post, $request ) {
            $ticket_price = get_post_meta( $post->ID, 'ticket_price', true );
            $response->data['ticket_price'] = apply_filters( 'rooftop_format_money', $ticket_price );
            return $response;
        }, 10, 3);

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        return $this->get_item( $request );
    }
    public function create_price( $request ) {
        $this->post_type = 'event_price';

        add_filter( "rest_pre_insert_{$this->post_type}", function( $prepared_post, $request) {
            $prepared_post->post_status = $request['status'] ? $request['status'] : 'publish';

            return $prepared_post;
        }, 10, 2);

        add_action( 'rest_insert_post', function( $prepared_post, $request, $success ) {
            if( $prepared_post->post_type === 'event_price' ) {
                update_post_meta( $prepared_post->ID, 'price_list_id', $request['price_list_id'] );

                $meta_data = $request['post_meta'];
                foreach($meta_data as $key => $value) {
                    update_post_meta( $prepared_post->ID, $key, $value );
                }

                return $prepared_post;
            }

            return $prepared_post;
        }, 10, 3);

        $required_keys = array( 'ticket_price', 'price_band_id', 'ticket_type_id' );

        $meta = $request['post_meta'] ? $request['post_meta'] : array();

        if( count( array_intersect_key( array_flip( $required_keys ), $meta ) ) == count( $required_keys ) ) {
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
        $this->post_type = 'event_price';

        add_filter( "rest_pre_insert_{$this->post_type}", function( $prepared_post, $request) {
            $meta_data = $request['post_meta'];
            foreach($meta_data as $key => $value) {
                if( empty( $value ) ) {
                    delete_post_meta( $prepared_post->ID, $key );
                }else {
                    update_post_meta( $prepared_post->ID, $key, $value );
                }
            }
            return $prepared_post;
        }, 10, 2);

        $price_response = $this->update_item( $request );

        return $price_response;
    }
    public function delete_price( $request ) {
        $this->post_type = 'event_price';
        return $this->delete_item( $request );
    }

    public function get_price_bands( $request ) {
        $this->post_type = 'event_price_band';

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        if( $context == 'embed' ) {
            $price_band_id = get_post_meta( $request['parent'], 'price_band_id', true );
            return $this->get_item( array( 'id' => $price_band_id, 'context' => 'embed' ) );
        }else {
            return $this->get_items( $request );
        }
    }
    public function get_price_band( $request ) {
        $this->post_type = 'event_price_band';
        return $this->get_item( $request );
    }
    public function update_price_band( $request ) {
        $this->post_type = 'event_price_band';
        return $this->update_item( $request );
    }
    public function delete_price_band( $request ) {
        $this->post_type = 'event_price_band';
        return $this->delete_item( $request );
    }
}
