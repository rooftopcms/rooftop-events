<?php

class Event_Instance extends Rooftop_Model {

    static function table_name() {
        global $wpdb;

        return $wpdb->prefix . "event_instances";
    }
}

?>