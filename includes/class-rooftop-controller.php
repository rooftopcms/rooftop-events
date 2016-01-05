<?php

class Rooftop_Controller extends WP_REST_Posts_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    function add_link_filters( $post_type ) {
        $filter_name = "rooftop_prepare_{$post_type}_links";
        $method_name = "{$post_type}_links_filter";

        if( method_exists( $this, $method_name ) ) {
            add_filter( $filter_name, array( $this, $method_name ), 10, 2 );
        }
    }


    /**
     * Prepare links for the request.
     *
     * @param WP_Post $post Post object.
     * @return array Links for the given post.
     */
    protected function prepare_links( $post ) {
        $links = parent::prepare_links( $post );

        $filter_name = "rooftop_prepare_{$post->post_type}_links";
        $links = apply_filters( $filter_name, $links, $post );

        return $links;
    }

}
