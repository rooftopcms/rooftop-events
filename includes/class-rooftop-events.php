<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://errorstudio.co.uk
 * @since      1.0.0
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/includes
 * @author     Error <info@errorstudio.co.uk>
 */
class Rooftop_Events {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rooftop_Events_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'rooftop-events';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rooftop_Events_Loader. Orchestrates the hooks of the plugin.
	 * - Rooftop_Events_i18n. Defines internationalization functionality.
	 * - Rooftop_Events_Admin. Defines all hooks for the admin area.
	 * - Rooftop_Events_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-events-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-events-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rooftop-events-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rooftop-events-public.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../rest-api/lib/endpoints/class-wp-rest-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../rest-api/lib/endpoints/class-wp-rest-posts-controller.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-events-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-event-instances-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-prices-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rooftop-tickets-controller.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'models/rooftop_model.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'models/event.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'models/event_instance.php';

		$this->loader = new Rooftop_Events_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rooftop_Events_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rooftop_Events_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rooftop_Events_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $plugin_admin, 'create_tables' );
        $this->loader->add_action( 'wpmu_new_blog', $plugin_admin, 'create_tables' );
        $this->loader->add_action( 'delete_blog', $plugin_admin, 'remove_tables', 20 );

        /**
         *
         */

        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_events_admin_ui' );

        /**
         *
         */

        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_event_price_list_meta_boxes', 10, 3 );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_event_price_meta_boxes', 10, 3 );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_event_instance_meta_boxes', 10, 3 );

        $this->loader->add_action( 'save_post', $plugin_admin, 'save_event', 10, 3 );
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_event_instance', 10, 3 );
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_event_price', 10, 3 );
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_event_price_list', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rooftop_Events_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $plugin_public, 'register_event_post_types' );

        $this->loader->add_filter( 'rest_query_vars', $plugin_public, 'allow_meta_query_args' );
        $this->loader->add_action( 'rest_api_init', $plugin_public, 'initialise_events_controller' );

        $this->loader->add_action( 'delete_post', $plugin_public, 'delete_event', 10, 1);
        $this->loader->add_action( 'delete_post', $plugin_public, 'delete_price_list', 10, 1);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Rooftop_Events_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
