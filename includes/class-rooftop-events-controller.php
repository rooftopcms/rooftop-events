<?php

class WP_REST_Events_Controller extends Rooftop_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    public function event_links_filter( $links, $post ) {
        $links['instances'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'events/' . $post->ID . '/instances'),
            'embeddable' => true
        );

        return $links;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        $base = $this->get_post_type_base( $this->post_type ) . 's';

        register_rest_route( 'rooftop-events/v2', '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_events' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_event' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            )
        ) );
        $this->add_link_filters('event');

        register_rest_route( 'rooftop-events/v2', '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_event' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_event' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_event' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            )
        ) );
    }

    public function get_events( $request ) {
        $this->post_type = 'event';
        return $this->get_items( $request );
    }
    public function get_event( $request ) {
        $this->post_type = 'event';

        return $this->get_item( $request );
    }
    public function create_event( $request ) {
        $this->post_type = 'event';

        add_filter( "rest_pre_insert_{$this->post_type}", function( $prepared_post, $request) {
            $content_attributes = $request['content'];
            if( $content_attributes && array_key_exists( 'content', $content_attributes['basic'] ) ) {
                $prepared_post->post_content = array_key_exists( 'content', $content_attributes['basic'] ) ? $content_attributes['basic']['content'] : $request['content'];
            }

            return $prepared_post;
        }, 10, 2);

        add_action( 'rest_insert_post', function( $prepared_post, $request, $success ) {
            if( $prepared_post->post_type === 'event' ) {
                $meta_data = $request['post_meta'];
                foreach($meta_data as $key => $value) {
                    update_post_meta( $prepared_post->ID, $key, $value );
                }

                return $prepared_post;
            }

            return $prepared_post;
        }, 10, 3);

        return $this->create_item( $request );
    }
    public function update_event( $request ) {
        $this->post_type = 'event';

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

        return $this->update_item( $request );
    }
    public function delete_event( $request ) {
        $this->post_type = 'event';
        return $this->delete_item( $request );
    }

}
