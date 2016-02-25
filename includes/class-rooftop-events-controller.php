<?php

class Rooftop_Events_Controller extends Rooftop_Controller {
    function __construct( $post_type ) {
        parent::__construct( $post_type );

        // add the event_id metadata to newly created event instance posts
        add_action( "rooftop_".$this->post_type."_rest_insert_post", function( $prepared_post, $request, $success ){
            update_post_meta( $prepared_post->ID, 'event_id', $request['event_id'] );

            $updated_meta = $request[$this->post_type."_meta"];

            // we need the event genre to be a top-level attribute (not a part of the event_meta array)
            // so that we can easily query related events that value
            if( $updated_meta && array_key_exists( 'genre', $updated_meta ) && strlen( $updated_meta['genre'] ) ) {
                update_post_meta( $request['id'], 'event_genre', $updated_meta['genre'] );
            }else {
                delete_post_meta( $request['id'], 'event_genre' );
            }

            return $prepared_post;
        }, 10, 3);
    }

    public function event_links_filter( $links, $post ) {
        $prefix = "rooftop-events/v2";
        $base = "$prefix/{$post->post_type}s";

        $links['instances'] = array(
            'href' => rest_url( $base . '/' . $post->ID . '/instances'),
            'embeddable' => true
        );
        $links['related_events'] = array(
            'href' => rest_url( $base . '/' . $post->ID . '/related_events'),
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

        register_rest_route( 'rooftop-events/v2', '/events/(?P<event_id>[\d]+)/update_metadata', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_event_metadata' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            )
        ) );

        register_rest_route( 'rooftop-events/v2', '/events/(?P<event_id>[\d]+)/related_events', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_related_events' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => $this->get_collection_params(),
            )
        ) );
    }

    public function get_events( $request ) {
        return $this->get_items( $request );
    }
    public function get_event( $request ) {
        return $this->get_item( $request );
    }
    public function create_event( $request ) {
        return $this->create_item( $request );
    }
    public function update_event( $request ) {
        return $this->update_item( $request );
    }
    public function delete_event( $request ) {
        return $this->delete_item( $request );
    }

    public function update_event_metadata( $request ) {
        $event_id = (int) $request['event_id'];

        do_action( 'rooftop_update_event_metadata', $event_id );

        return $this->get_item( array(
            'id'      => $event_id,
            'context' => 'edit',
        ));
    }

    public function get_related_events( $request ) {
        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $url = wp_parse_url($_SERVER['REQUEST_URI']);
        $events_index = preg_match( '/events$/', $url['path'] );

        if( $context === 'embed' && $events_index ) {
            return []; // when hitting the events index, we don't need the related events data
        }

        $event_id = $request['event_id'];
        $genre    = get_post_meta( $event_id, 'event_genre', true );

        $events_in_genre_args = array(
            'post_type' => 'event',
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'post__not_in' => array( $event_id ),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'event_genre',
                    'value' => $genre,
                    'compare' => '='
                ),
                array(
                    'key' => 'last_event_instance',
                    'value' => date('Y-m-d H:i:s'),
                    'compare' => '>='
                )
            )

        );

        $related_events = get_posts( $events_in_genre_args );

        $related_event_ids = array_map( function( $event ) {
            return $event->ID;
        }, $related_events );

        $number_of_related_events = count( $related_event_ids ) >= 3 ? 3 : count( $related_event_ids );

        if( $number_of_related_events ) {
            $related_event_indexes = array_rand( $related_event_ids , $number_of_related_events );
            $related_event_ids = array_intersect_key( $related_event_ids, array_flip( $related_event_indexes ) );

            $request->set_param( 'filter', array(
                'orderby' => 'meta_value_num',
                'meta_key' => 'last_event_instance',
                'order' => 'asc',
                'post__in' => array_values( $related_event_ids ),
                'post_type' => 'event',
                'post__not_in' => array( $event_id ))
            );

            return $this->get_items( $request );
        }else {
            return [];
        }
    }
}
