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
        register_post_type('event', array(
            'labels' => array('name' => 'Events', 'singular_name' => 'Event'),
            'public' => true,
            'has_archive' => true
        ));
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
