<?php

interface TableModel {
    static function table_name();
}

abstract class Rooftop_Model implements TableModel {
    public $id, $attributes;

    function __construct($attributes = null, $after_find = false) {
        $this->attributes = $attributes;

        if( count( func_get_args() ) > 0 && is_array( $this->attributes ) ) {
            foreach( $this->attributes as $key => $value ) {
                $this->$key = $value;
            }
        }

        if( (property_exists( $this, 'id' ) && $this->id ) && method_exists( $this, 'after_find' ) ) {
            $this->after_find();
        }

        return $this;
    }

    function set($attribute, $value) {
        $this->attributes[$attribute] = $value;
    }

    function save() {
        if( $this->id ) {
            self::updateRow($this->id, $this->attributes);
        }else {
            $this->id = self::createRow($this->attributes);
        }
    }

    static function createRow($data) {
        global $wpdb;

        $class = get_called_class();
        $table_name = call_user_func(array(__NAMESPACE__ . "\\" . $class, "table_name"));

        $wpdb->insert($table_name, $data);

        return $wpdb->insert_id;
    }

    static function updateRow($id, $data) {
        global $wpdb;

        $class = get_called_class();
        $table_name = call_user_func(array(__NAMESPACE__ . "\\" . $class, "table_name"));

        $wpdb->update($table_name, $data, array('id' => $id));
    }

    static function all() {
        return self::findWhere("1=1");
    }

    static function find($id) {
        global $wpdb;

        $class = get_called_class();
        $table_name = call_user_func(array(__NAMESPACE__ . "\\" . $class, "table_name"));

        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id LIMIT 1", ARRAY_A);

        return new $class($row, true);
    }

    static function findWhere($condition) {
        global $wpdb;

        $class = get_called_class();
        $table_name = call_user_func(array(__NAMESPACE__ . "\\" . $class, "table_name"));

        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE $condition", ARRAY_A);
        $objects = [];

        foreach($results as $row) {
            $objects[] = new $class($row, true);
        }

        return $objects;
    }

}

?>