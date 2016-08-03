<?php


class Rooftop_Controller extends WP_REST_Posts_Controller {

    public $post_type;

    public function __construct( $post_type ) {
        parent::__construct($post_type);

        $this->post_type = $post_type;

        $this->register_routes();
        $this->add_update_attribute_hooks();
        $this->add_rooftop_link_filters();

        $this->add_rooftop_rest_presentation_filters();
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

    /**
     *
     */
    protected function add_update_attribute_hooks() {
        add_filter( "rest_pre_insert_{$this->post_type}", function( $prepared_post, $request) {
            if ( isset($request['status'] ) ) {
                $prepared_post->post_status = $request['status'];
            }

            $content_attributes = $request['content'];
            if( $content_attributes && array_key_exists( 'content', $content_attributes['basic'] ) ) {
                $prepared_post->post_content = array_key_exists( 'content', $content_attributes['basic'] ) ? $content_attributes['basic']['content'] : $request['content'];
            }

            return $prepared_post;
        }, 10, 2);

        add_action( 'rest_insert_post', function( $prepared_post, $request, $success ) {
            if( $prepared_post->post_type === $this->post_type ) {
                $meta_data_key = $this->post_type."_meta";
                $meta_data = $request[$meta_data_key];

                if( $meta_data ) {
                    $current_meta_data = get_post_meta( $prepared_post->ID, $meta_data_key, true );
                    $current_meta_data = ( is_array($current_meta_data) ? $current_meta_data : Array() );

                    $custom_attributes = Array();

                    foreach($meta_data as $key => $value) {
                        if( empty( $value ) ) {
                            unset( $current_meta_data[$key] );
                        }else {
                            if( is_array( $value ) ) {
                                $old_value = @$current_meta_data[$key];
                                $old_value = ( $old_value && is_array( $old_value ) ) ? $old_value : [];
                                $value = array_merge( $old_value, $value );

                                if( empty( array_filter( $value ) ) ) {
                                    unset( $current_meta_data[$key] );
                                    continue;
                                }else {
                                    foreach( $value as $k => $v ) {
                                        if( empty( $v ) ) unset( $value[$k] );
                                    }
                                }
                            }

                            $custom_attributes[$key] = $value;
                        }

                        unset($key);
                        unset($value);
                    }

                    $updated_custom_attributes = array_merge( $current_meta_data, $custom_attributes );
                    update_post_meta( $prepared_post->ID, $prepared_post->post_type."_meta", $updated_custom_attributes );
                }

                do_action( "rooftop_".$this->post_type."_rest_insert_post", $prepared_post, $request, $success );
            }

            return $prepared_post;
        }, 10, 3);
    }

    function add_rooftop_rest_presentation_filters() {
        add_filter( "rest_prepare_".$this->post_type, function( $response, $post, $request ) {
            $custom_attributes = get_post_meta( $post->ID, $post->post_type."_meta", false );

            if( $custom_attributes && count( $custom_attributes ) ) {
                foreach( $custom_attributes[0] as $key => $value ) {
                    $response->data[$this->post_type.'_meta'][$key] = $value;
                }
            }

            do_action( "rooftop_".$this->post_type."_rest_presentation_filters" );

            return $response;
        }, 10, 3);

    }
}
