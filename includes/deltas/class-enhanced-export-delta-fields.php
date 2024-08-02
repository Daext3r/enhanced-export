<?php
require_once 'class-enhanced-export-delta.php';
class Enhanced_Export_Delta_Custom_Fields extends Enhanced_Export_Delta
{
    public function get_custom_fields() {
        global $wpdb;

        $custom_fields = $wpdb->get_results(
            "SELECT * FROM {$this->custom_fields_table}",
            OBJECT
        );

        return $custom_fields;
    }

    public function get_custom_field_query($field_id) {
        global $wpdb;

        $q = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT query FROM {$this->custom_fields_table} WHERE id = %d",
                $field_id
            )
        );

        return $q;
    }

    public function list_custom_fields() {
        global $wpdb;

        $custom_fields = $wpdb->get_results(
            "SELECT id, name FROM {$this->custom_fields_table}",
            OBJECT
        );

        $fields = [];

        foreach($custom_fields as $field) {
            $fields["$field->id"] = $field->name;
        }

        return $fields;
    }

    public function save_custom_fields($fields) {
        global $wpdb;
        
        if(!is_array($fields)) {
            return false;
        }

        foreach($fields as $field) {
            if(!is_array($field)) {
                //skip registry if its not an array
                continue;
            }

            if(isset($field['id']) && !empty($field['id'])) {
                //updates the field
                $wpdb->update(
                    $this->custom_fields_table,
                    array(
                        'name' => $field['name'],
                        'query' => $field['query']
                    ), 
                    array(
                        'id' => $field['id']
                    )
                );

            } else {
                //creates new field
                $wpdb->insert(
                    $this->custom_fields_table,
                    array(
                        'name' => $field['name'],
                        'query' => $field['query']
                    )
                );
            }
        }

        return true;

        
    }
}
