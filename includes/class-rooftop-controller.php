<?php


class Rooftop_Controller extends WP_REST_Posts_Controller {

    public $post_type;

    public function __construct( $post_type ) {
        parent::__construct($post_type);

        $this->post_type = $post_type;

        $this->register_routes();
        $this->add_rooftop_link_filters();
    }

    function add_rooftop_link_filters() {
        $filter_name = "rooftop_prepare_{$this->post_type}_links";
        $method_name = "{$this->post_type}_links_filter";

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
