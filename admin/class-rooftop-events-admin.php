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

    public function add_events_admin_ui() {
        add_meta_box( 'rooftop_event_instances_link', 'Event Instances', array($this, 'rooftop_event_instances'), 'event', 'normal', 'default' );
    }

    public function highlight_events_menu($parent_file) {
        global $submenu_file;

        if( preg_match( '/edit\.php?post_type=event/', $parent_file ) ){
            return true;
        }

        return $parent_file;
    }

    public function add_event_price_list_meta_boxes() {
        add_meta_box('event_price_list_prices', "Prices", function(){
            $price_list_args = array(
                'meta_key' => 'price_list_id',
                'meta_value' => get_the_ID(),
                'post_type' => 'event_price',
                'post_status' => 'publish',
                'posts_per_page' => -1
            );

            $prices = get_posts($price_list_args);
            require_once plugin_dir_path( __FILE__ ) . 'partials/rooftop-event-price-list-price-index.php';

        }, 'event_price_list');
    }
    public function save_event_price_list($post_id, $post, $update) {
        if( 'event_price_list' != $post->post_type ) return;

        if( $_POST && array_key_exists('rooftop', $_POST) ) {
            update_post_meta($post_id, 'ticket_price', $_POST['rooftop']['price_list']['ticket_price']);
            update_post_meta($post_id, 'price_band_id', (int)$_POST['rooftop']['price_list']['price_band']);
            update_post_meta($post_id, 'ticket_type_id', (int)$_POST['rooftop']['price_list']['ticket_type']);
        }
    }

    public function add_event_price_meta_boxes() {
        add_meta_box('event_instance_price_field', 'Ticket Price', function() {
            $ticket_price = get_post_meta(get_the_ID(), 'ticket_price', true);
            echo "£<input type=\"text\" name=\"rooftop[price_list][ticket_price]\" value=\"$ticket_price\"/>";
        }, 'event_price');

        add_meta_box('event_price_list', "Price List", function() {
            global $post;

            $price_lists_args = array(
                'post_type' => 'event_price_list',
                'post_status' => 'publish',
                'posts_per_page' => -1
            );
            $price_lists = get_posts($price_lists_args);

            $rooftop_price_list_id = get_post_meta($post->ID, 'price_list_id', true);

            if( !$rooftop_price_list_id && count($price_lists) ) {
                $rooftop_price_list_id = array_key_exists('event_price_list_id', $_GET) ? $_GET['event_price_list_id'] :  array_values($price_lists)[0]->ID;

                $this->renderSelect("rooftop[price_list][price_list_id]", $price_lists, $rooftop_price_list_id);
            }else {
                $price_list = get_post($rooftop_price_list_id);
                echo "<a href=\"/wp-admin/post.php?post=$price_list->ID&action=edit\">$price_list->post_title</a>";
            }

        }, 'event_price');

        add_meta_box('event_instance_ticket_band', 'Ticket Band', function() {
            $ticket_bands_args = array(
                'post_type' => 'event_price_band',
                'post_status' => 'publish',
                'orderby' => 'ID',
                'order' => 'ASC',
                'posts_per_page' => -1
            );
            $ticket_bands = get_posts($ticket_bands_args);
            $selected = (int)get_post_meta(get_the_ID(), 'price_band_id', true);

            $this->renderSelect("rooftop[price_list][price_band]", $ticket_bands, $selected);
        }, 'event_price');

        add_meta_box('event_instance_ticket_type', 'Ticket Type', function() {
            $ticket_types_args = array(
                'post_type' => 'event_ticket_type',
                'post_status' => 'publish',
                'orderby' => 'ID',
                'order' => 'ASC',
                'posts_per_page' => -1
            );
            $ticket_types = get_posts($ticket_types_args);
            $selected = (int)get_post_meta(get_the_ID(), 'ticket_type_id', true);

            $this->renderSelect("rooftop[price_list][ticket_type]", $ticket_types, $selected);
        }, 'event_price');
    }

    public function save_event_price($post_id, $post, $update) {
        if( 'event_price' != $post->post_type ) return;

        if( $_POST && array_key_exists('rooftop', $_POST) ) {

            $price_list_id = get_post_meta($post_id, 'price_list_id', true);
            if( !$price_list_id ) {
                $price_list_id = (array_key_exists('rooftop', $_POST) && array_key_exists('price_list', $_POST['rooftop'])) ? (int)$_POST['rooftop']['price_list']['price_list_id'] : null;

                if( !$price_list_id ) {
                    return new WP_Error(422, "Unprocessible entity");
                    echo "No price list ID provided";
                    exit;
                }

                update_post_meta($post_id, 'price_list_id', $price_list_id);

            }

            global $wpdb;
            $table = $wpdb->prefix.'posts';
            $price_list = get_post($price_list_id);
            $price_post_title = 'Price: ' . $_POST['rooftop']['price_list']['ticket_price'] . ', Price List: ' . $price_list->post_title;

            $wpdb->query($wpdb->prepare("UPDATE $table SET post_title = %s WHERE ID = %d", $price_post_title, $post_id));

            update_post_meta($post_id, 'ticket_price', (int)$_POST['rooftop']['price_list']['ticket_price']);
            update_post_meta($post_id, 'price_band_id', (int)$_POST['rooftop']['price_list']['price_band']);
            update_post_meta($post_id, 'ticket_type_id', (int)$_POST['rooftop']['price_list']['ticket_type']);
        }
    }

    public function add_event_instance_meta_boxes() {
        add_meta_box('event_instance_event', 'Event', function() {
            global $post;

            $rooftop_event_id = get_post_meta($post->ID, 'event_id', true);

            $event_posts_args = array(
                'post_type' => 'event',
                'post_status' => 'publish'
            );
            $event_posts = get_posts($event_posts_args);

            if( !$rooftop_event_id && count($event_posts) ) {
                $rooftop_event_id = array_key_exists('event_id', $_GET) ? $_GET['event_id'] :  array_values($event_posts)[0]->ID;

                $this->renderSelect("rooftop[event][event_id]", $event_posts, $rooftop_event_id);
            }else {
                $event = get_post($rooftop_event_id);
                echo "<a href=\"/wp-admin/post.php?post=$event->ID&action=edit\">$event->post_title</a>";
            }
        }, 'event_instance', 'side');

        add_meta_box('event_instance_price_list', 'Price Lists', function() {
            $price_lists_args = array(
                'post_type' => 'event_price_list',
                'post_status' => 'publish',
                'posts_per_page' => -1
            );
            $price_lists = get_posts($price_lists_args);

            $event_instance_price_list_id = get_post_meta( get_the_ID(), 'price_list_id', true );
            if( is_array( $event_instance_price_list_id ) && count( $event_instance_price_list_id ) ) {
                $event_instance_price_list_id = $event_instance_price_list_id[0];
            }

            $this->renderSelect("rooftop[event_instance][price_list_id]", $price_lists, $event_instance_price_list_id);
        }, 'event_instance', 'side');

        add_meta_box('event_instance_details', 'Event Instances', function() {
            $formatted_date = function($time) {
                $date = new DateTime();
                return $date->setTimestamp($time)->format("d-m-Y H:i:s");
            };

            if( array_key_exists('post', $_GET) ) {
                $instance = get_post($_GET['post']);
                $instance_meta = get_post_meta($instance->ID, 'event_instance_availability', true);
                // ensure the event_instance_availability is an array (get_post_meta will return "" if the post didn't have anything stored against it previously
                $instance_meta =     is_array($instance_meta) ? $instance_meta : [];

                $starts_at = array_key_exists('starts_at', $instance_meta) ? $instance_meta['starts_at'] : $formatted_date(time());
                $stops_at  = array_key_exists('stops_at', $instance_meta)  ? $instance_meta['stops_at'] : $formatted_date(time());
                $capacity  = array_key_exists('seats_capacity', $instance_meta)  ? $instance_meta['seats_capacity'] : 0;
                $available = array_key_exists('seats_available', $instance_meta) ? $instance_meta['seats_available'] : 0;
            }else {
                $starts_at = $formatted_date(time());
                $stops_at  = $formatted_date(time());

                $capacity  = 0;
                $available = 0;
            }

            echo "<table class='table' style='width: 100%'>";
            echo "    <tr>";
            echo "        <td>Start Date<br/>       <input type='text' value='".$starts_at."' name='rooftop[event_instance][starts_at]' /></td>";
            echo "        <td>End Date<br/>         <input type='text' value='".$stops_at."'  name='rooftop[event_instance][stops_at]' /></td>";
            echo "        <td>Seating Capacity<br/> <input type='text' value='".$capacity."'  name='rooftop[event_instance][seats_capacity]' /></td>";
            echo "        <td>Seats Available<br/>  <input type='text' value='".$available."' name='rooftop[event_instance][seats_available]' /></td>";
            echo "    </tr>";
            echo "</table>";
        }, 'event_instance', 'normal', 'high');
    }

    public function save_event_instance($post_id, $post, $update) {
        if( 'event_instance' != $post->post_type ) return;

        // save this instance with a corresponding event_id, so that we can lookup an event's instances using a WP meta query
        $event_id = get_post_meta($post_id, 'event_id', true);

        if( !$event_id ) {
            $event_id = (array_key_exists('rooftop', $_POST) && array_key_exists('event_instance', $_POST['rooftop'])) ? (int)$_POST['rooftop']['event']['event_id'] : null;

            if( !$event_id ) {
                return new WP_Error(422, "Unprocessible entity");
                echo "No event ID provided";
                exit;
            }

            update_post_meta($post_id, 'event_id', $event_id);
        }

        if( $_POST ) {
            if( array_key_exists( 'price_list_id', $_POST['rooftop']['event_instance'] ) ) {
                $price_list_id = $_POST['rooftop']['event_instance']['price_list_id'];
            }else {
                $price_list_id = null;
            }

            update_post_meta($post_id, 'event_instance_availability', $_POST['rooftop']['event_instance']);
            update_post_meta($post_id, 'price_list_id', $price_list_id);
        }
    }

    public function save_event($post_id, $post, $update) {
        if( 'event' != $post->post_type ) return;

        $rooftop_event_id = get_post_meta($post_id, 'rooftop_event_id', true);

        if( !$rooftop_event_id ) {
            $rooftop_event = new Event(array('post_id' => $post_id));
            $rooftop_event->save();

            update_post_meta($post_id, 'rooftop_event_id', $rooftop_event->id);
        }
    }

    function rooftop_event_instances() {
        $event_instance_args = array(
            'meta_key' => 'event_id',
            'meta_value' => get_the_ID(),
            'post_type' => 'event_instance',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        $event_instances = get_posts( $event_instance_args );
        require_once plugin_dir_path( __FILE__ ) . 'partials/rooftop-event-instances-index.php';
    }

    private function renderSelect( $name, $collection, $selected = null, $options = array() ) {
        require plugin_dir_path( __FILE__ ) . 'partials/_select.php';
        unset($collection);
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
