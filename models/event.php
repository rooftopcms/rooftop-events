<?php

class Event extends Rooftop_Model {

    static function table_name() {
        global $wpdb;

        return $wpdb->prefix . "events";
    }
}

?>