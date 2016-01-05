<?php

class WP_REST_Event_Instances_Controller extends Rooftop_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    public function event_instance_links_filter( $links, $post ) {
        $links['price_list'] = array(
            'href' => rest_url( 'rooftop-events/v2/' . 'price_lists?parent=' . $post->ID),
            'embeddable' => true
        );

        return $links;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        $base = $this->get_post_type_base( $this->post_type ) . 's';

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
        $this->add_link_filters('event_instance');

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
        $this->post_type = 'event_instance';

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
        $this->post_type = 'event_instance';

        add_filter( "rest_prepare_".$this->post_type, function( $response, $post, $request ) {
            $availability = get_post_meta( $post->ID, 'event_instance_availability', false );

            if( count( $availability ) ) {
                foreach( $availability[0] as $key => $value ) {
                    $response->data[$key] = $value;
                }
            }

            return $response;
        }, 10, 3);

        return $this->get_item( $request );
    }
    public function create_event_instance( $request ) {
        $this->post_type = 'event_instance';

        add_filter( "rest_pre_insert_{$this->post_type}", function( $prepared_post, $request) {
            $prepared_post->post_status = $request['status'] ? $request['status'] : 'publish';

            $content_attributes = $request['content'];
            if( $content_attributes && array_key_exists( 'content', $content_attributes['basic'] ) ) {
                $prepared_post->post_content = array_key_exists( 'content', $content_attributes['basic'] ) ? $content_attributes['basic']['content'] : $request['content'];
            }

            return $prepared_post;
        }, 10, 2);

        add_action( 'rest_insert_post', function( $prepared_post, $request, $success ) {
            if( $prepared_post->post_type === 'event_instance' ) {
                update_post_meta( $prepared_post->ID, 'event_id', $request['event_id'] );

                $meta_data = $request['post_meta'];
                foreach($meta_data as $key => $value) {
                    update_post_meta( $prepared_post->ID, $key, $value );
                }
            }

            return $prepared_post;
        }, 10, 3);

        return $this->create_item( $request );
    }
    public function update_event_instance( $request ) {
        $this->post_type = 'event_instance';
        return $this->update_item( $request );
    }
    public function delete_event_instance( $request ) {
        $this->post_type = 'event_instance';
        return $this->delete_item( $request );
    }
}
