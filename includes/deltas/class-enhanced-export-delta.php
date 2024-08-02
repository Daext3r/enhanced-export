<?php


class Enhanced_Export_Delta {
    public $exports_table = '';
    public $templates_table = '';
    public $custom_fields_table = '';


    public function __construct() {
        global $wpdb;

        $this->exports_table = $wpdb->prefix . 'ee_exports';
        $this->templates_table = $wpdb->prefix . 'ee_templates';
        $this->custom_fields_table = $wpdb->prefix . 'ee_custom_fields';
    }

    public function validate_export($export_id)
    {
        global $wpdb;

        $export = $wpdb->get_var("SELECT id FROM $this->exports_table WHERE id = $export_id");

        return !is_null($export);
    }
}