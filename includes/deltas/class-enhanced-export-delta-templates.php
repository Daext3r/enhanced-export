<?php
require_once 'class-enhanced-export-delta.php';
class Enhanced_Export_Delta_Templates extends Enhanced_Export_Delta {

    public function list_templates() {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->templates_table", OBJECT);
    }
    public function create_template_from_export($export_id) {
        //get export fields

        global $ee_exports_delta;
        $data = $ee_exports_delta->get_export($export_id);

        $fields = $data->fields;
        $filters = $data->filters;

        global $wpdb;

        $wpdb->insert(
            $this->templates_table,
            [
                'name' => 'Unnamed template from export #' . $export_id,
                'fields' => $fields,
                'filters' => $filters,
            ]
        );
    }

    public function delete_template($template_id) {
        global $wpdb;
        $wpdb->delete(
            $this->templates_table,
            [
                'id' => $template_id
            ]
        );
    }

    public function rename_template($template_id, $name) {
        global $wpdb;
        $wpdb->update(
            $this->templates_table,
            [
                'name' => $name
            ],
            [
                'id' => $template_id
            ]
        );
    }

    public function get_template_by_id($template_id) {
        global $wpdb;  

        return $wpdb->get_row("SELECT * FROM $this->templates_table WHERE id = $template_id", OBJECT);
    }

}



