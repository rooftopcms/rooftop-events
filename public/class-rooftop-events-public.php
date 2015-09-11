<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://errorstudio.co.uk
 * @since      1.0.0
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/public
 * @author     Error <info@errorstudio.co.uk>
 */
class Rooftop_Events_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rooftop_Events_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rooftop_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rooftop-events-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rooftop_Events_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rooftop_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rooftop-events-public.js', array( 'jquery' ), $this->version, false );

	}

    public function register_event_post_type() {
        $labels = array(
            'name'                => _x( 'Events', 'Post Type General Name', 'text_domain' ),
            'singular_name'       => _x( 'Event', 'Post Type Singular Name', 'text_domain' ),
            'menu_name'           => __( 'Events', 'text_domain' ),
            'name_admin_bar'      => __( 'Event', 'text_domain' ),
            'parent_item_colon'   => __( 'Parent Event:', 'text_domain' ),
            'all_items'           => __( 'All Event', 'text_domain' ),
            'add_new_item'        => __( 'Add New Event', 'text_domain' ),
            'add_new'             => __( 'Add New', 'text_domain' ),
            'new_item'            => __( 'New Event', 'text_domain' ),
            'edit_item'           => __( 'Edit Event', 'text_domain' ),
            'update_item'         => __( 'Update Event', 'text_domain' ),
            'view_item'           => __( 'View Event', 'text_domain' ),
            'search_items'        => __( 'Search Event', 'text_domain' ),
            'not_found'           => __( 'Not found', 'text_domain' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
        );
        $args = array(
            'label'               => __( 'Event', 'text_domain' ),
            'description'         => __( 'Event Description', 'text_domain' ),
            'labels'              => $labels,
            'supports'            => array( ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'event', $args );
    }

    public function register_event_routes() {
        $routes['/events'] = array(
            array( array( $this, 'get_posts'), WP_REST_Server::READABLE ),
            array( array( $this, 'new_post'), WP_REST_Server::CREATABLE | WP_REST_Server::ACCEPT_JSON ),
        );
        $routes['/events/(?P<id>\d+)'] = array(
            array( array( $this, 'get_post'), WP_REST_Server::READABLE ),
            array( array( $this, 'edit_post'), WP_REST_Server::EDITABLE | WP_REST_Server::ACCEPT_JSON ),
            array( array( $this, 'delete_post'), WP_REST_Server::DELETABLE ),
        );

        return $routes;
    }

    public function get_posts() {
    }
    public function get_post() {
        return get_post(5);
    }
    public function new_post() {

    }
    public function edit_post() {

    }
    public function delete_post() {

    }
}
