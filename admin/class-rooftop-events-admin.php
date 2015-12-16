<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://errorstudio.co.uk
 * @since      1.0.0
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/admin
 * @author     Error <info@errorstudio.co.uk>
 */
class Rooftop_Events_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rooftop-events-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rooftop-events-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * @param $blog_id
     *
     * when called via the wpmu_create_blog hook, we'll be given a blog id.
     * in other cases, when called via init we have no $blog_id, but may still
     * need to create the tables
     */
    public function create_tables($blog_id = null) {
        if( (defined('DOING_AJAX') && DOING_AJAX == "DOING_AJAX") || (array_key_exists( 'action', $_REQUEST ) && $_REQUEST['action'] == 'heartbeat' )) {
            return;
        }


        if( !$blog_id ) {
            $blog_id = get_current_blog_id();
        }

        $option_key = "site_${blog_id}_event_tables_added";
        $tables_added = (bool)get_site_option($option_key);

        if( !$tables_added ) {
            try {
                $this->create_event_tables();
                update_site_option( $option_key, true );
            }catch(Exception $e) {
                new WP_Error(500, "Something went wrong when creating the events database!");
                exit;
            }
        }

        restore_current_blog();
    }

    public function remove_event_instances_from_menu() {
        remove_menu_page('edit.php?post_type=event_instances');
    }

    public function add_events_admin_ui() {
        add_meta_box( 'rooftop_event_instances_link', 'Event Instances', array($this, 'rooftop_event_instances'), 'event', 'normal', 'default' );
    }

    public function save_event($post_id, $post, $update) {
        if( 'event' != $post->post_type ) return;

        $f = 1;
    }

    public function save_event_instance($post_id, $post, $update) {
        if( 'event_instance' != $post->post_type ) return;

        $f = 1;
    }

    function rooftop_event_instances() {
        $event_instance_args = array(
            'meta_query' => array(
                'key' => 'event_id',
                'value' => get_the_ID()
            ),
            'post_type' => 'event_instance',
            'posts_per_page' => -1
        );

        $event_instances = get_posts($event_instance_args);
        require_once plugin_dir_path( __FILE__ ) . 'partials/rooftop-event-instances-index.php';
    }

    private function create_event_tables() {
        global $wpdb;
        $wpdb->query("START TRANSACTION");

        $event_table_name     = $wpdb->prefix . "events";
        $event_table_name_sql = <<<EOSQL
CREATE TABLE $event_table_name(
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    post_id INTEGER NOT NULL,
PRIMARY KEY(id)
)
EOSQL;
        dbDelta($event_table_name_sql);
        $added = $wpdb->get_var("SHOW TABLES LIKE '$event_table_name'") == $event_table_name;
        if( !$added ){
            $wpdb->query("ROLLBACK");
            throw new Exception("Couldn't create table $event_table_name");
        }

        $instance_table_name     = $wpdb->prefix . "event_instances";
        $instance_table_name_sql = <<<EOSQL
CREATE TABLE $instance_table_name(
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    event_id INTEGER NOT NULL,
    starts_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    stops_at  DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    seats_selected INTEGER DEFAULT 0,
    seats_available INTEGER DEFAULT 0,
    seats_capacity INTEGER DEFAULT 0,
    seats_locked INTEGER DEFAULT 0,
PRIMARY KEY(id)
)
EOSQL;
        dbDelta($instance_table_name_sql);
        $added = $wpdb->get_var("SHOW TABLES LIKE '$instance_table_name'") == $instance_table_name;
        if( !$added ){
            $wpdb->query("ROLLBACK");
            throw new Exception("Couldn't create table $event_table_name");
        }

        $price_table_name     = $wpdb->prefix . "event_instance_prices";
        $price_table_name_sql = <<<EOSQL
CREATE TABLE $price_table_name(
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    event_instance_id INTEGER NOT NULL,
    price INTEGER DEFAULT 0,
PRIMARY KEY(id)
)
EOSQL;
        dbDelta($price_table_name_sql);
        $added = $wpdb->get_var("SHOW TABLES LIKE '$price_table_name'") == $price_table_name;
        if( !$added ){
            $wpdb->query("ROLLBACK");
            throw new Exception("Couldn't create table $price_table_name_sql");
        }

        $wpdb->query("COMMIT");
    }

    public function remove_tables($blog_id) {
        global $wpdb;

        $event_table_name    = $wpdb->prefix . "events";
        $instance_table_name = $wpdb->prefix . "event_instances";
        $price_table_name    = $wpdb->prefix . "event_instance_prices";

        $sql = <<<EOSQL
DROP TABLE $event_table_name;
DROP TABLE $instance_table_name;
DROP TABLE $price_table_name;
EOSQL;
        $wpdb->query($sql);

    }

}
