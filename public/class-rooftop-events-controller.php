<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://errorstudio.co.uk
 * @since      1.0.0
 *
 * @package    Rooftop_Events
 * @subpackage Rooftop_Events/controller
 */

/**
 */
class Rooftop_Events_Controller {
    protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type = $post_type;
    }

    public function register_routes() {
        $f = 1;
    }
}
