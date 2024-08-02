<?php

require_once 'class-enhanced-export-delta.php';

class Enhanced_Export_Delta_Exports extends Enhanced_Export_Delta
{   

    private function get_raw_filters()
    {
        //used for overwrite custom filters
        $std = new stdClass();
        $std->post_types = [];

        $std->date_filters = new stdClass();
        $std->date_filters->from = '';
        $std->date_filters->to = '';

        $std->taxonomy_filters = [];
        return $std;
    }

    private function get_raw_fields()
    {
        //used for overwrite custom filters
        $std = new stdClass();
        $std->basic = [];
        $std->taxes = [];
        $std->meta = [];
        $std->custom = [];
        
        return $std;

    }

    // Helper functions
    public function get_error_response($response)
    {
        $std = new stdClass();
        $std->success = false;
        $std->response = $response;

        return $std;
    }
    public function get_success_response($response)
    {
        $std = new stdClass();
        $std->success = true;
        $std->response = $response;

        return $std;
    }

    public function set_status($export_id, $status)
    {

        //TODO: Validate export and status
        global $wpdb;

        $wpdb->update(
            $this->exports_table,
            array(
                'status' => $status
            ),
            array(
                'id' => $export_id,
            )
        );
    }

    public function list_exports($page = 1, $orderby = 'id', $order = 'desc')
    {
        $offset = ($page - 1) * 20;

        $query = "SELECT * from $this->exports_table ORDER BY $orderby $order LIMIT $offset, 20";

        global $wpdb;
        $results = $wpdb->get_results($query, OBJECT);

        return $results;
    }

    public function get_export_pages_count() {

        $query = "SELECT COUNT(*) from $this->exports_table";

        global $wpdb;
        $count = $wpdb->get_var($query);

        $pages = $count % 20 == 0 ? $count / 20 : intval($count / 20) + 1;

        return $pages;
    }


    public function get_export_filters($export_id)
    {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        global $wpdb;

        $query = "SELECT `filters` from {$this->exports_table} WHERE id = %d;";

        $filters = $wpdb->get_var($wpdb->prepare($query, $export_id));

        if (is_null($filters)) {
            $filters = $this->get_raw_filters();
        } else {
            $filters = json_decode($filters, false);
        }

        return $filters;
    }


    public function get_export_fields($export_id)
    {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        global $wpdb;

        $query = "SELECT `fields` from {$this->exports_table} WHERE id = %d;";

        $fields = $wpdb->get_var($wpdb->prepare($query, $export_id));

        if (is_null($fields)) {
            $fields = $this->get_raw_fields();
        } else {
            $fields = json_decode($fields, false);
        }

        return $fields;
    }

    public function get_export_basic_fields($export_id) {

        $fields = $this->get_export_fields($export_id);

        return $fields->basic;
    }

    public function set_export_filters($export_id, $filters)
    {
        global $wpdb;

        $updated_rows = $wpdb->update(
            $this->exports_table,
            array(
                'filters' => json_encode($filters),
            ),
            array(
                'id' => $export_id,
            )
        );

        return $updated_rows;
    }

    public function set_export_fields($export_id, $fields)
    {
        global $wpdb;

        $updated_rows = $wpdb->update(
            $this->exports_table,
            array(
                'fields' => json_encode($fields),
            ),
            array(
                'id' => $export_id,
            )
        );

        return $updated_rows;
    }

    public function create_export($export_name)
    {

        //validates the export name is not empty
        if (empty($export_name)) {
            return $this->get_error_response('Invalid export name');
        }

        global $wpdb;

        //creates the export in the database
        $result = $wpdb->insert($this->exports_table, ['name' => $export_name, 'file_name' => str_replace(' ', '_', $export_name) . '.csv'],['%s', '%s']);


        if (!$result) {
            //Error when inserting
            return $this->get_error_response('Error when creating export');
        }

        //succcess creating export, returns export_id's
        return $this->get_success_response((object) ['insert_id' => $wpdb->insert_id]);
    }

    public function apply_template_to_export($export_id, $template_id) {
        global $ee_templates_delta;

        $data = $ee_templates_delta->get_template_by_id($template_id);

        $fields = json_decode($data->fields, false);
        $filters = json_decode($data->filters, false);

        if (!$data) {
            //Error when retrieving template data
            return $this->get_error_response('Error when retrieving template data');
        }

        $this->set_export_fields($export_id, $fields);
        $this->set_export_filters($export_id, $filters);
    }

    public function delete_export($export_id)
    {
        global $wpdb;

        //get export's filename
        $export = $this->get_export($export_id);

        $file_path = WP_CONTENT_DIR . '/enhanced-export/exports/' . $export->file_name;
        
        unlink($file_path);

        $wpdb->delete(
            $this->exports_table,
            array(
                'id' => $export_id,
            )
        );
    }

    public function get_export($export_id)
    {
        global $wpdb;

        $export_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->exports_table} where id = %d", $export_id), OBJECT);

        return $export_data;
    }


    public function set_export_post_types($export_id, $post_types)
    {

        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //check if the post_types key exists
        if (empty($post_types)) {
            return $this->get_error_response('Invalid post type(s)');
        }

        //get current filters
        $filters = $this->get_export_filters($export_id);

        //validates each post type is registered
        foreach ($post_types as $post_type) {
            if (!post_type_exists($post_type)) {
                return $this->get_error_response("Unknown post type: $post_type");
            }
        }

        $filters->post_types = $post_types;

        $result = $this->set_export_filters($export_id, $filters);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }

    public function get_export_post_types($export_id) {
          //valdate the $export_id is not empty
          if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $filters = $this->get_export_filters($export_id);

        return $filters->post_types;

    }

    public function set_export_date_filter($export_id, $date_from, $date_to)
    {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $filters = $this->get_export_filters($export_id);

        $filters->date_filters->from = $date_from;
        $filters->date_filters->to = $date_to;

        $result = $this->set_export_filters($export_id, $filters);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }

    public function set_export_basic_fields($export_id, $basic_fields)
    {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $fields = $this->get_export_fields($export_id);

        $fields->basic = $basic_fields;

        $result = $this->set_export_fields($export_id, $fields);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }

    
    public function set_export_meta_fields($export_id, $meta_fields) {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $fields = $this->get_export_fields($export_id);

        $fields->meta = $meta_fields;

        $result = $this->set_export_fields($export_id, $fields);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }


    public function set_export_custom_fields($export_id, $custom_fields) {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $fields = $this->get_export_fields($export_id);

        $fields->custom = $custom_fields;

        $result = $this->set_export_fields($export_id, $fields);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }

    public function set_export_taxes_fields($export_id, $taxes_fields) {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        $fields = $this->get_export_fields($export_id);

        $fields->taxes = $taxes_fields;

        $result = $this->set_export_fields($export_id, $fields);

        return $this->get_success_response((object) ['updated_rows' => $result]);
    }


    public function calculate_initial_records($export_id)
    {
        //valdate the $export_id is not empty
        if (empty($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }
 
        //valiates that the export exists
        if (!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        require_once dirname(__FILE__, 2) . '/class-enhanced-export-exporter.php';

        $exporter = new Enhanced_Export_Exporter();

        $query_args = $exporter->generate_query_args($export_id);

        $query = new WP_Query(
            $query_args
        );

        //get the total count of posts affected by the query
        $count = $query->found_posts;

        global $wpdb;

        //stores the count

        $wpdb->update(
            $this->exports_table,
            array(
                'records' => $count,
                'processed' => 0
            ),
            array(
                'id' => $export_id,
            )
        );       
    }

    public function increase_processed_records($export_id, $count) 
    {
        global $wpdb; 

        $export = $this->get_export($export_id);

        $wpdb->update(
            $this->exports_table,
            array(
                'processed' => (intval($export->processed) + $count)
            ),
            array(
                'id' => $export_id,
            )
        );     
    }
}
