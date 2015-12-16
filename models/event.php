<?php

class Event extends Rooftop_Model {

    function table_name() {
        global $wpdb;

        return $wpdb->prefix . "events";
    }

    function after_find() {
        $this->event_instances = Event_Instance::findWhere("event_id = ". $this->id);
    }
}

?>