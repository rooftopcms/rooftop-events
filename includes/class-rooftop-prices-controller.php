<?php

class WP_REST_Prices_Controller extends WP_REST_Controller {

    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
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
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_list' ),
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<price_list_id>[\d]+)/prices', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_prices' ),
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/price_lists/(?P<price_list_id>[\d]+)/prices/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price' ),
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/price_bands', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_bands' ),
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
        register_rest_route( 'rooftop-events/v2', '/price_bands/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_price_band' ),
                'permission_callback' => array( $this, 'get_event_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            )
        ) );
    }


    private function prepare_price_for_response( $post, $request ) {
        $data = array(
            'id'           => $post->ID,
            'date'         => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
            'date_gmt'     => $this->prepare_date_response( $post->post_date_gmt ),
            'guid'         => array(
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            ),
            'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
            'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),
            'slug'         => $post->post_name,
            'status'       => $post->post_status,
            'type'         => $post->post_type,
            'title'        => array('rendered' => $post->post_title),
            'link'         => get_permalink( $post->ID ),
        );

        // Wrap the data in a response object.
        $response = new WP_REST_Response( $data, 200 );

        $response->add_links( $this->prepare_links( $post ) );

        return apply_filters( 'rest_prepare_' . $post->post_type, $response, $post, $request );
    }

    public function get_price_lists( $request ) {
        $args                   = array();
        $args['author']         = $request['author'];
        $args['paged']          = $request['page'];
        $args['posts_per_page'] = $request['per_page'];
        $args['post_parent']    = $request['parent'];
        $args['post_status']    = $request['status'];
        $args['s']              = $request['search'];

        if ( is_array( $request['filter'] ) ) {
            $args = array_merge( $args, $request['filter'] );
            unset( $args['filter'] );
        }

        // Force the post_type argument, since it's not a user input variable.
        $args['post_type'] = 'event_price_list';

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        /**
         * Filter the query arguments for a request.
         *
         * Enables adding extra arguments or setting defaults for a post
         * collection request.
         *
         * @param array           $args    Key value array of query var to query value.
         * @param WP_REST_Request $request The request used.
         */
        $args = apply_filters( 'rest_post_query', $args, $request );
        $query_args = $this->prepare_events_query( $args );

        if( $context == 'embed' && $args['post_parent'] ) {
            $price_list_id = (int) get_post_meta( $args['post_parent'], 'price_list_id', true );

            $posts = get_posts( array( 'post_type' => 'event_price_list', 'post__in' => array( $price_list_id ) ) );
        }else {
            $posts_query = new WP_Query();
            $posts = $posts_query->query( $query_args );
        }

        $price_lists = array();
        foreach ( $posts as $price_list ) {
            if ( ! $this->check_read_permission( $price_list ) ) {
                continue;
            }

            $data = $this->prepare_price_list_for_response( $price_list, $request );
            $price_lists[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $price_lists );

        return $response;
    }
    public function get_price_list( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( empty( $id ) || empty( $post->ID ) || 'event_price_list' !== $post->post_type ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        $data = $this->prepare_price_list_for_response( $post, $request );
        $response = rest_ensure_response( $data );

        $response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );

        return $response;
    }
    public function get_prices( $request ) {
        $args                   = array();
        $args['author']         = $request['author'];
        $args['paged']          = $request['page'];
        $args['posts_per_page'] = $request['per_page'];
        $args['post_parent']    = $request['parent'];
        $args['post_status']    = $request['status'];
        $args['s']              = $request['search'];
        $args['meta_key']       = 'price_list_id';
        $args['meta_value']     = $request['price_list_id'];

        if ( is_array( $request['filter'] ) ) {
            $args = array_merge( $args, $request['filter'] );
            unset( $args['filter'] );
        }

        // Force the post_type argument, since it's not a user input variable.
        $args['post_type'] = 'event_price';

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        /**
         * Filter the query arguments for a request.
         *
         * Enables adding extra arguments or setting defaults for a post
         * collection request.
         *
         * @param array           $args    Key value array of query var to query value.
         * @param WP_REST_Request $request The request used.
         */
        $args = apply_filters( 'rest_post_query', $args, $request );
        $query_args = $this->prepare_events_query( $args );

        $posts_query = new WP_Query();
        $query_result = $posts_query->query( $query_args );

        $price_lists = array();

        foreach ( $query_result as $price_list ) {
            if ( ! $this->check_read_permission( $price_list ) ) {
                continue;
            }

            $data = $this->prepare_price_for_response( $price_list, $request );
            $price_lists[] = $this->prepare_response_for_collection( $data );
        }

        $response = new WP_REST_Response( $price_lists, 200 );

        return $response;
    }
    public function get_price( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( empty( $id ) || empty( $post->ID ) || 'event_price' !== $post->post_type ) {
            return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
        }

        $data = $this->prepare_price_for_response( $post, $request );
        $data->data['ticket_price'] = get_post_meta( $post->ID, 'ticket_price', true );

        $price_band_id = get_post_meta( $post->ID, 'price_band_id', true );
        $ticket_type_id = get_post_meta( $post->ID, 'ticket_type_id', true );

        $price_band = get_post( $price_band_id );
        $ticket_type = get_post( $ticket_type_id );

        $data->data['price_band'] = $price_band->post_title;
        $data->data['ticket_type'] = $ticket_type->post_title;

        $response = rest_ensure_response( $data );

        $response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );

        return $response;
    }

    public function get_price_bands( $request ) {
        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        $args = array(
            'post_type'  => 'event_price_band',
        );

        if( $context == 'embed' ) {
            $args['meta_key'] = 'price_band_id';
            $args['meta_value'] = (int) $request['parent'];
        }

        $posts = get_posts( $args );
        $price_bands = array();
        foreach ( $posts as $price_band ) {
            if ( ! $this->check_read_permission( $price_band ) ) {
                continue;
            }

            $data = $this->prepare_price_band_for_response( $price_band, $request );
            $price_lists[] = $this->prepare_response_for_collection( $data );
        }

        $response = new WP_REST_Response( $price_bands, 200 );

        return $response;
    }

    public function get_price_band( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );


        if( empty( $id ) || empty( $post->ID) || $post->post_type !== 'event_price_band' ) {
            return new WP_Error( 'rest_post_invalid_id', 'Invalid post id', array( 'status' => 404 ) );
        }

        $data = $this->prepare_price_band_for_response( $post, $request );
        $price_band = $this->prepare_response_for_collection( $data );

        $response = new WP_REST_Response( $price_band, 200 );

        return $response;
    }

    private function prepare_price_list_for_response( $post, $request ) {
        $data = array(
            'id'           => $post->ID,
            'date'         => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
            'date_gmt'     => $this->prepare_date_response( $post->post_date_gmt ),
            'guid'         => array(
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            ),
            'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
            'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),
            'slug'         => $post->post_name,
            'status'       => $post->post_status,
            'type'         => $post->post_type,
            'title'        => array('rendered' => $post->post_title),
            'link'         => get_permalink( $post->ID ),
        );

        $response = new WP_REST_Response( $data, 200 );

        $response->add_links( $this->prepare_links( $post ) );

        return apply_filters( 'rest_prepare_' . $post->post_type, $response, $post, $request );
    }

    private function prepare_price_band_for_response( $post, $request ) {
        $data = array(
            'id'           => $post->ID,
            'date'         => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
            'date_gmt'     => $this->prepare_date_response( $post->post_date_gmt ),
            'guid'         => array(
                /** This filter is documented in wp-includes/post-template.php */
                'rendered' => apply_filters( 'get_the_guid', $post->guid ),
                'raw'      => $post->guid,
            ),
            'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
            'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),
            'slug'         => $post->post_name,
            'status'       => $post->post_status,
            'type'         => $post->post_type,
            'title'        => array('rendered' => $post->post_title),
            'link'         => get_permalink( $post->ID ),
        );

        // Wrap the data in a response object.
        $response = new WP_REST_Response( $data, 200 );

        $response->add_links( $this->prepare_links( $post ) );

        return apply_filters( 'rest_prepare_' . $post->post_type, $response, $post, $request );
    }


    /**
     * Check if a given request has access to read /posts.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function get_events_permissions_check( $request ) {

        $post_type = get_post_type_object( $this->post_type );

        if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit these posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }

    /**
     * Check if a given request has access to read a post.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function get_event_permissions_check( $request ) {

        $post = get_post( (int) $request['id'] );

        if ( 'edit' === $request['context'] && $post && ! $this->check_update_permission( $post ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this post' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if ( $post ) {
            return $this->check_read_permission( $post );
        }

        return true;
    }

    /**
     * Check if a given request has access to create a post.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function create_event_permissions_check( $request ) {

        $post_type = get_post_type_object( $this->post_type );

        if ( ! empty( $request['password'] ) && ! current_user_can( $post_type->cap->publish_posts ) ) {
            return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create password protected posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if ( ! empty( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
            return new WP_Error( 'rest_cannot_edit_others', __( 'You are not allowed to create posts as this user.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if ( ! empty( $request['sticky'] ) && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
            return new WP_Error( 'rest_cannot_assign_sticky', __( 'You do not have permission to make posts sticky.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return current_user_can( $post_type->cap->create_posts );
    }

    /**
     * Check if a given request has access to update a post.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function update_event_permissions_check( $request ) {

        $post = get_post( $request['id'] );
        $post_type = get_post_type_object( $this->post_type );

        if ( $post && ! $this->check_update_permission( $post ) ) {
            return false;
        }

        if ( ! empty( $request['password'] ) && ! current_user_can( $post_type->cap->publish_posts ) ) {
            return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create password protected posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if ( ! empty( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
            return new WP_Error( 'rest_cannot_edit_others', __( 'You are not allowed to update posts as this user.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if ( ! empty( $request['sticky'] ) && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
            return new WP_Error( 'rest_cannot_assign_sticky', __( 'You do not have permission to make posts sticky.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }

    /**
     * Check if a given request has access to delete a post.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function delete_event_permissions_check( $request ) {

        $post = get_post( $request['id'] );

        if ( $post && ! $this->check_delete_permission( $post ) ) {
            return new WP_Error( 'rest_cannot_delete', __( 'Sorry, you are not allowed to delete posts.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }

    /**
     * Determine the allowed query_vars for a get_events() response and
     * prepare for WP_Query.
     *
     * @param array $prepared_args
     * @return array $query_args
     */
    protected function prepare_events_query( $prepared_args = array() ) {

        $valid_vars = array_flip( $this->get_allowed_query_vars() );
        $query_args = array();
        foreach ( $valid_vars as $var => $index ) {
            if ( isset( $prepared_args[ $var ] ) ) {
                /**
                 * Filter the query_vars used in `get_events` for the constructed query.
                 *
                 * The dynamic portion of the hook name, $var, refers to the query_var key.
                 *
                 * @param mixed $prepared_args[ $var ] The query_var value.
                 *
                 */
                $query_args[ $var ] = apply_filters( "rest_query_var-{$var}", $prepared_args[ $var ] );
            }
        }

        if ( empty( $query_args['post_status'] ) && 'attachment' === $this->post_type ) {
            $query_args['post_status'] = 'inherit';
        }

        if ( 'post' !== $this->post_type || ! isset( $query_args['ignore_sticky_posts'] ) ) {
            $query_args['ignore_sticky_posts'] = true;
        }

        return $query_args;
    }


    /**
     * Get all the WP Query vars that are allowed for the API request.
     *
     * @return array
     */
    protected function get_allowed_query_vars() {
        global $wp;

        /**
         * Filter the publicly allowed query vars.
         *
         * Allows adjusting of the default query vars that are made public.
         *
         * @param array  Array of allowed WP_Query query vars.
         */
        $valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

        if ( current_user_can( 'edit_posts' ) ) {
            /**
             * Filter the allowed 'private' query vars for authorized users.
             *
             * If the user has the `edit_posts` capability, we also allow use of
             * private query parameters, which are only undesirable on the
             * frontend, but are safe for use in query strings.
             *
             * To disable anyway, use
             * `add_filter( 'rest_private_query_vars', '__return_empty_array' );`
             *
             * @param array $private_query_vars Array of allowed query vars for authorized users.
             * }
             */
            $private = apply_filters( 'rest_private_query_vars', $wp->private_query_vars );
            $valid_vars = array_merge( $valid_vars, $private );
        }
        // Define our own in addition to WP's normal vars.
        $rest_valid = array( 'posts_per_page', 'ignore_sticky_posts', 'post_parent' );
        $valid_vars = array_merge( $valid_vars, $rest_valid );

        /**
         * Filter allowed query vars for the REST API.
         *
         * This filter allows you to add or remove query vars from the final allowed
         * list for all requests, including unauthenticated ones. To alter the
         * vars for editors only, {@see rest_private_query_vars}.
         *
         * @param array {
         *    Array of allowed WP_Query query vars.
         *
         *    @param string $allowed_query_var The query var to allow.
         * }
         */
        $valid_vars = apply_filters( 'rest_query_vars', $valid_vars );

        return $valid_vars;
    }

    /**
     * Check the post excerpt and prepare it for single post output.
     *
     * @param string       $excerpt
     * @return string|null $excerpt
     */
    protected function prepare_excerpt_response( $excerpt ) {
        if ( post_password_required() ) {
            return __( 'There is no excerpt because this is a protected post.' );
        }

        /** This filter is documented in wp-includes/post-template.php */
        $excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $excerpt ) );

        if ( empty( $excerpt ) ) {
            return '';
        }

        return $excerpt;
    }

    /**
     * Check the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     *
     * @param string       $date_gmt
     * @param string|null  $date
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    protected function prepare_date_response( $date_gmt, $date = null ) {
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }

        if ( isset( $date ) ) {
            return mysql_to_rfc3339( $date );
        }

        return mysql_to_rfc3339( $date_gmt );
    }

    protected function prepare_password_response( $password ) {
        if ( ! empty( $password ) ) {
            /**
             * Fake the correct cookie to fool post_password_required().
             * Without this, get_the_content() will give a password form.
             */
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $hasher = new PasswordHash( 8, true );
            $value = $hasher->HashPassword( $password );
            $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = wp_slash( $value );
        }

        return $password;
    }

    /**
     * Determine validity and normalize provided status param.
     *
     * @param string $post_status
     * @param object $post_type
     * @return WP_Error|string $post_status
     */
    protected function handle_status_param( $post_status, $post_type ) {
        $post_status = $post_status;

        switch ( $post_status ) {
            case 'draft':
            case 'pending':
                break;
            case 'private':
                if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
                    return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create private posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
                }
                break;
            case 'publish':
            case 'future':
                if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
                    return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to publish posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
                }
                break;
            default:
                if ( ! get_post_status_object( $post_status ) ) {
                    $post_status = 'draft';
                }
                break;
        }

        return $post_status;
    }

    /**
     * Determine validity and normalize provided author param.
     *
     * @param object|integer $post_author
     * @param object $post_type
     * @return WP_Error|integer $post_author
     */
    protected function handle_author_param( $post_author, $post_type ) {
        if ( is_object( $post_author ) ) {
            if ( empty( $post_author->id ) ) {
                return new WP_Error( 'rest_invalid_author', __( 'Invalid author object.' ), array( 'status' => 400 ) );
            }
            $post_author = (int) $post_author->id;
        } else {
            $post_author = (int) $post_author;
        }

        // Only check edit others' posts if we are another user.
        if ( get_current_user_id() !== $post_author ) {

            $author = get_userdata( $post_author );

            if ( ! $author ) {
                return new WP_Error( 'rest_invalid_author', __( 'Invalid author id.' ), array( 'status' => 400 ) );
            }
        }

        return $post_author;
    }

    /**
     * Determine the featured image based on a request param.
     *
     * @param int $featured_image
     * @param int $post_id
     */
    protected function handle_featured_image( $featured_image, $post_id ) {

        $featured_image = (int) $featured_image;
        if ( $featured_image ) {
            $result = set_post_thumbnail( $post_id, $featured_image );
            if ( $result ) {
                return true;
            } else {
                return new WP_Error( 'rest_invalid_featured_image', __( 'Invalid featured image id.' ), array( 'status' => 400 ) );
            }
        } else {
            return delete_post_thumbnail( $post_id );
        }

    }

    /**
     * Set the template for a page.
     *
     * @param string $template
     * @param integer $post_id
     */
    public function handle_template( $template, $post_id ) {
        if ( in_array( $template, array_keys( wp_get_theme()->get_page_templates( get_post( $post_id ) ) ) ) ) {
            update_post_meta( $post_id, '_wp_page_template', $template );
        } else {
            update_post_meta( $post_id, '_wp_page_template', '' );
        }
    }

    /**
     * Check if a given post type should be viewed or managed.
     *
     * @param object|string $post_type
     * @return bool Is post type allowed?
     */
    protected function check_is_post_type_allowed( $post_type ) {
        if ( ! is_object( $post_type ) ) {
            $post_type = get_post_type_object( $post_type );
        }

        if ( ! empty( $post_type ) && $post_type->show_in_rest ) {
            return true;
        }

        return false;
    }

    /**
     * Check if we can read a post.
     *
     * Correctly handles posts with the inherit status.
     *
     * @param object $post Post object.
     * @return bool Can we read it?
     */
    public function check_read_permission( $post ) {
        if ( ! empty( $post->post_password ) && ! $this->check_update_permission( $post ) ) {
            return false;
        }

        $post_type = get_post_type_object( $post->post_type );
        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        // Can we read the post?
        if ( 'publish' === $post->post_status || current_user_can( $post_type->cap->read_post, $post->ID ) ) {
            return true;
        }

        $post_status_obj = get_post_status_object( $post->post_status );
        if ( $post_status_obj && $post_status_obj->public ) {
            return true;
        }

        // Can we read the parent if we're inheriting?
        if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
            $parent = get_post( $post->post_parent );

            if ( $this->check_read_permission( $parent ) ) {
                return true;
            }
        }

        // If we don't have a parent, but the status is set to inherit, assume
        // it's published (as per get_post_status()).
        if ( 'inherit' === $post->post_status ) {
            return true;
        }

        return false;
    }

    /**
     * Check if we can edit a post.
     *
     * @param object $post Post object.
     * @return bool Can we edit it?
     */
    protected function check_update_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return current_user_can( $post_type->cap->edit_post, $post->ID );
    }

    /**
     * Check if we can create a post.
     *
     * @param object $post Post object.
     * @return bool Can we create it?.
     */
    protected function check_create_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return current_user_can( $post_type->cap->create_posts );
    }

    /**
     * Check if we can delete a post.
     *
     * @param object $post Post object.
     * @return bool Can we delete it?
     */
    protected function check_delete_permission( $post ) {
        $post_type = get_post_type_object( $post->post_type );

        if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
            return false;
        }

        return current_user_can( $post_type->cap->delete_post, $post->ID );
    }

    /**
     * Get the base path for a post type's endpoints.
     *
     * @param object|string $post_type
     * @return string       $base
     */
    public function get_post_type_base( $post_type ) {
        if ( ! is_object( $post_type ) ) {
            $post_type = get_post_type_object( $post_type );
        }

        $base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;

        return $base;
    }

    /**
     * Prepare links for the request.
     *
     * @param WP_Post $post Post object.
     * @return array Links for the given post.
     */
    protected function prepare_links( $post ) {
        $base = '/rooftop-events/v2/' . $this->get_post_type_base( $post->post_type ).'s';

        // Entity meta
        $links = array(
            'self' => array(
                'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
            ),
            'collection' => array(
                'href'   => rest_url( $base ),
            ),
            'about'      => array(
                'href'   => rest_url( '/wp/v2/types/' . $this->post_type ),
            ),
        );

        if ( ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'author' ) )
            && ! empty( $post->post_author ) ) {
            $links['author'] = array(
                'href'       => rest_url( '/wp/v2/users/' . $post->post_author ),
                'embeddable' => true,
            );
        };

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'comments' ) ) {
            $replies_url = rest_url( '/wp/v2/comments' );
            $replies_url = add_query_arg( 'post', $post->ID, $replies_url );
            $links['replies'] = array(
                'href'         => $replies_url,
                'embeddable'   => true,
            );
        }

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'revisions' ) ) {
            $links['version-history'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/revisions' ),
            );
        }
        $post_type_obj = get_post_type_object( $post->post_type );
        if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) ) {
            $links['up'] = array(
                'href'       => rest_url( trailingslashit( $base ) . (int) $post->post_parent ),
                'embeddable' => true,
            );
        }

        // If we have a featured image, add that.
        if ( $featured_image = get_post_thumbnail_id( $post->ID ) ) {
            $image_url = rest_url( 'wp/v2/media/' . $featured_image );
            $links['https://api.w.org/featuredmedia'] = array(
                'href'       => $image_url,
                'embeddable' => true,
            );
        }
        if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ) ) ) {
            $attachments_url = rest_url( 'wp/v2/media' );
            $attachments_url = add_query_arg( 'parent', $post->ID, $attachments_url );
            $links['https://api.w.org/attachment'] = array(
                'href'       => $attachments_url,
            );
        }

        $taxonomies = get_object_taxonomies( $post->post_type );
        if ( ! empty( $taxonomies ) ) {
            $links['https://api.w.org/term'] = array();

            foreach ( $taxonomies as $tax ) {
                $taxonomy_obj = get_taxonomy( $tax );
                // Skip taxonomies that are not public.
                if ( false === $taxonomy_obj->public ) {
                    continue;
                }

                $tax_base = ! empty( $taxonomy_obj->rest_base ) ? $taxonomy_obj->rest_base : $tax;
                $terms_url = rest_url( trailingslashit( $base ) . $post->ID . '/' . $tax_base );

                $links['https://api.w.org/term'][] = array(
                    'href'       => $terms_url,
                    'taxonomy'   => $tax,
                    'embeddable' => true,
                );
            }
        }

        if ( post_type_supports( $post->post_type, 'custom-fields' ) ) {
            $links['https://api.w.org/meta'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/meta' ),
                'embeddable' => true,
            );
        }

        if ( in_array( $post->post_type, array( 'event' ) ) ) {
            $links['instances'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/instances' ),
                'embeddable' => true
            );
        }

        if ( in_array( $post->post_type, array( 'event_price_list' ) ) ) {
            $links['prices'] = array(
                'href' => rest_url( 'rooftop-events/v2/price_lists/' . $post->ID . '/prices' ),
                'embeddable' => true
            );
        }

        if ( in_array( $post->post_type, array( 'event_price' ) ) ) {
            $links['ticket_type'] = array(
                'href' => rest_url( 'rooftop-events/v2/ticket_types?parent=' . $post->ID ),
                'embeddable' => true
            );
            $links['price_band'] = array(
                'href' => rest_url( 'rooftop-events/v2/price_bands?parent=' . $post->ID ),
                'embeddable' => true
            );
        }

        return $links;
    }

    /**
     * Get the query params for collections of attachments.
     *
     * @return array
     */
    public function get_collection_params() {
        $params = parent::get_collection_params();

        $params['context']['default'] = 'view';

        if ( post_type_supports( $this->post_type, 'author' ) ) {
            $params['author'] = array(
                'description'         => 'Limit result set to posts assigned to a specific author.',
                'type'                => 'integer',
                'default'             => null,
                'sanitize_callback'   => 'absint',
            );
        }
        $params['order'] = array(
            'description'        => 'Order sort attribute ascending or descending.',
            'type'               => 'string',
            'default'            => 'asc',
            'enum'               => array( 'asc', 'desc' ),
        );
        $params['orderby'] = array(
            'description'        => 'Sort collection by object attribute.',
            'type'               => 'string',
            'default'            => 'name',
            'enum'               => array(
                'id',
                'title',
                'slug',
            ),
        );
        $params['status'] = array(
            'default'           => 'publish',
            'description'       => 'Limit result set to posts assigned a specific status.',
            'sanitize_callback' => 'sanitize_key',
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_user_can_query_private_statuses' ),
        );
        $params['filter'] = array();
        return $params;
    }

    /**
     * Validate whether the user can query private statuses
     *
     * @param  mixed $value
     * @param  WP_REST_Request $request
     * @param  string $parameter
     * @return WP_Error|bool
     */
    public function validate_user_can_query_private_statuses( $value, $request, $parameter ) {
        if ( 'publish' === $value ) {
            return true;
        }
        $post_type_obj = get_post_type_object( $this->post_type );
        if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
            return true;
        }
        return new WP_Error( 'rest_forbidden_status', __( 'Status is forbidden' ), array( 'status' => rest_authorization_required_code() ) );
    }

    private function prepare_prices_for_response( $prices, $request ) {
        $prices_array = [];

        foreach( $prices as $price ) {
            $prices_array[] = $this->prepare_price_for_response( $price, $request );
        }

        return $prices_array;
    }
}
