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

    public function register_event_post_types() {
        $types = array(
            'event' => array('plural' => 'Events', 'singular' => 'Event', 'custom_args' => null),
            'event_instance'   => array('plural' => 'Event Instances',    'singular' => 'Event Instance',    'custom_args' => array('show_in_menu'=>'edit.php?post_type=event')),
            'event_price_list' => array('plural' => 'Event Price Lists',  'singular' => 'Event Price List',  'custom_args' => array('show_in_menu'=>'edit.php?post_type=event')),
            'event_price'      => array('plural' => 'Event Prices',       'singular' => 'Event Price',       'custom_args' => array('show_in_menu'=>'edit.php?post_type=event', 'supports' => false)),
            'event_price_band' => array('plural' => 'Event Price Bands',  'singular' => 'Event Price Band',  'custom_args' => array('show_in_menu'=>'edit.php?post_type=event')),
            'event_ticket_type' => array('plural' => 'Event Ticket Types', 'singular' => 'Event Ticket Type', 'custom_args' => array('show_in_menu'=>'edit.php?post_type=event')),
        );

        foreach($types as $type => $args) {
            list($plural, $singular, $custom_args) = array_values($args);

            $menu_item_name_prefix = (is_array( $custom_args ) && array_key_exists( 'show_in_menu', $custom_args )) ? "" : "All ";

            $labels = array(
                'name'                => _x( $plural, 'Post Type General Name', 'text_domain' ),
                'singular_name'       => _x( $singular, 'Post Type Singular Name', 'text_domain' ),
                'menu_name'           => __( $plural, 'text_domain' ),
                'name_admin_bar'      => __( $singular, 'text_domain' ),
                'parent_item_colon'   => __( "Parent $singular:", 'text_domain' ),
                'all_items'           => __( "$menu_item_name_prefix$plural", 'text_domain' ),
                'add_new_item'        => __( "Add New $singular", 'text_domain' ),
                'add_new'             => __( "Add New $singular", 'text_domain' ),
                'new_item'            => __( "New $singular", 'text_domain' ),
                'edit_item'           => __( "Edit $singular", 'text_domain' ),
                'update_item'         => __( "Update $singular", 'text_domain' ),
                'view_item'           => __( "View $singular", 'text_domain' ),
                'search_items'        => __( "Search $singular", 'text_domain' ),
                'not_found'           => __( "Not found", 'text_domain' ),
                'not_found_in_trash'  => __( "Not found in Trash", 'text_domain' ),
            );
            $post_args = array(
                'label'               => __( "$plural", 'text_domain' ),
                'description'         => __( "$singular Description", 'text_domain' ),
                'labels'              => $labels,
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
                'show_in_rest'        => true
            );

            if( $custom_args ){
                $post_args = array_merge( $post_args, $custom_args );
            }

            register_post_type( $type, $post_args );
        }
    }

    public function initialise_events_controller() {
        $events       = new Rooftop_Events_Controller('event');
        $instances    = new Rooftop_Event_Instances_Controller('event_instance');
        $price_list   = new Rooftop_Price_Lists_Controller('event_price_list');
        $prices       = new Rooftop_Prices_Controller('event_price');
        $ticket_types = new Rooftop_Tickets_Controller('event_ticket_type');
        $price_bands  = new Rooftop_Price_Bands_Controller('event_price_band');

        $events->register_routes();
        $instances->register_routes();
        $price_list->register_routes();
        $prices->register_routes();
        $ticket_types->register_routes();
        $price_bands->register_routes();
    }

    public function allow_meta_query_args( $valid_vars ) {
        $valid_vars = array_merge( $valid_vars, array( 'meta_key', 'meta_value' ) );

        return $valid_vars;
    }

    public function delete_event( $event_id ) {
        $this->delete_associated_posts( $event_id, 'event_instance', 'event_id' );
    }

    public function delete_price_list( $price_list_id ) {
        $this->delete_associated_posts( $price_list_id, 'event_price', 'price_list_id' );
    }

    private function delete_associated_posts( $parent_id, $type, $key ) {
        $results = $this->get_associated_posts( $parent_id, $type, $key );

        foreach( $results as $result ) {
            wp_delete_post( $result->ID );
        }
    }
    private function get_associated_posts( $parent_id, $type, $key ) {
        $associated_post_args = array(
            'meta_key' => $key,
            'meta_value' => $parent_id,
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        return get_posts( $associated_post_args );
    }
}
