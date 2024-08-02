<?php

class Enhanced_Export_API_Controller {
    /**
	 * The namespace of REST API endpoints.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $namespace    The namespace name.
	 */
    private $namespace = 'enhanced-export';

    /**
	 * The version of REST API controller.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_version    The version of the REST API controller.
	 */
    private $api_version = 'v1';

    private $exports_table = '';

    private $templates_table = '';

    public $api_prefix = '';

    public function __construct() {
        global $wpdb;

        $this->exports_table = $wpdb->prefix . 'ee_exports';
        $this->templates_table = $wpdb->prefix . 'ee_templates';
        $this->api_prefix = $this->namespace . '/' . $this->api_version;
    }

    private function get_raw_filters() {
        //used for overwrite custom filters
        return array(
            'post_types' => [],
            'date_filters' => [],
            'taxonomy_filters' => []

        );
    }
    
    public function get_error_response($response) {return json_encode(['success' => false, 'response' => $response]);}
    public function get_success_response($response) {return json_encode(['success' => true, 'response' => $response]);}

    private function validate_request($request) {
        //TODO: Add validation
        return true;
    }

    private function validate_export($export_id) {
        global $wpdb;

        $export = $wpdb->get_var("SELECT id FROM $this->exports_table WHERE id = $export_id");

        return !is_null($export);
    }

    public function create_export($request) {
        //Validate request
        if(!$this->validate_request($request)) {
            return $this->get_error_response('Go away!');
        }

        //Validate data
        $export_name = $request['ee-export-name'];

        if(empty($export_name)) {
            return $this->get_error_response('Invalid input');
        }
        
        global $wpdb;

        $result = $wpdb->insert($this->exports_table, ['name' => $export_name, 'file_name' => str_replace(' ', '_', $export_name)],['%s', '%s']);
         
        if(!$result) {
            //Error when inserting
            $this->get_error_response('Error when creating export');
        }
        
        return $this->get_success_response(['insert_id' => $wpdb->insert_id]);
    } 

    
    public function update_export_filters($request) {
        //Validate request
        if(!$this->validate_request($request)) {
            return $this->get_error_response('Go away!');
        }

        if(!isset($request['export_id']) || empty($request['export_id'])) {
            return $this->get_error_response('Invalid export ID');
        }

        $export_id = $request['export_id'];

        if(!$this->validate_export($export_id)) {
            return $this->get_error_response('Invalid export ID');
        }

        //check if the post_types key exists
        if(!isset($request['post_types']) || empty($request['post_types'])) {
            return $this->get_error_response('Invalid post type(s)');
        }


        $filters = $this->get_raw_filters();

        if(isset($request['ee-post-types']) && !empty($request['ee-post-types'])) {
            $post_types = explode(',', $request['ee-post-types']);

            foreach( $post_types as $post_type ) {
                if(!post_type_exists($post_type)) {
                    return $this->get_error_response('Unknown post type');
                }
            }

            $filters['post_types'] = $post_types;
        }

        global $wpdb;

        $wpdb->update(
            $this->exports_table,
            array(
                'filters' => $filters,
        
            ),
            array(
                'id' => $export_id,
            )
        );
       
    }


}
