<?php


class Enhanced_Export_Exporter {
    private $exports_folder;

    public function __construct() {
        $this->exports_folder = WP_CONTENT_DIR . '/enhanced-export/exports/';

        // Creates the exports folder
        if(!file_exists($this->exports_folder )) {
            mkdir($this->exports_folder, 0755, true);
        }

        //Includes the delta
        require_once 'deltas/class-enhanced-export-delta-exports.php';
    }

    public function create_file($export_id) {
        global $ee_exports_delta;

        $export_data = $ee_exports_delta->get_export($export_id);

        if($export_data !== false) {
            //creates the filename
            $filename = $this->exports_folder . '/' . $export_data->file_name;

            if(!file_exists($filename)) {
                //creates the header for the CSV
                $header = $this->create_header($ee_exports_delta->get_export_fields($export_id)) . PHP_EOL;
            
                //saves the header
                $this->save_data($filename, $header);
                            
            }
        }
    }

    /**
     * Function that creates the header of the CSV
     */
    private function create_header($fields) {

        $basic_fields = $fields->basic;
        $meta_fields = $fields->meta;
        $tax_fields = $fields->taxes;
        $custom_fields = $fields->custom;

        $header = '';

        if(!empty($basic_fields)) {

            
            $friendly_names = [
                'ID' => 'ID del contenido',
                'post_title' => 'Título',
                'post_name' => 'Slug',
                'link' => 'URL',
                'post_date' => 'Fecha',
                'posts_status' => 'Estado',
                'post_excerpt' => 'Extracto',
                'post_type' => 'Tipo',
                'post_content' => 'Contenido'    
            ];
            
            $field_names = [];
            
            foreach($basic_fields as $field) {
                $field_names[] = $friendly_names[$field];
            }
            
            $header .= implode(',', $field_names);
        }
                
        if(!empty($tax_fields)) {
            if(!empty($header)) $header .= ',';
            
            $field_names = [];

            foreach($tax_fields as $tax) {
                $taxonomy = get_taxonomy($tax);
                $field_names[] = $taxonomy->labels->name;
            }
            
            $header .= implode(',', $field_names); 
        }

        if(!empty($meta_fields)) {
            $header .= ',' . implode(',', $meta_fields);
        }

        if(!empty($custom_fields)) {
            //$custom_fields is an array of custom fields ids. we need to retrieve the custom fields' names
            global $ee_custom_fields_delta;

            $all_fields = $ee_custom_fields_delta->list_custom_fields();

            $field_names = [];

            foreach($custom_fields as $field_id) {
                $field_names[] = $all_fields[$field_id];
            }

            $header .= ',' . implode(',', $field_names);
        }

        return $header;
    }

    /**
     * Function that saves the data into the file
     */
    private function save_data($filename, $data) {
        return file_put_contents($filename, $data, FILE_APPEND);
    }

    /**
     * Generates the query args to be used during the export and to calculate the initial results count
     */
    public function generate_query_args($export_id) {
        global $ee_exports_delta; 
        global $wpdb;

        $fields = $ee_exports_delta->get_export_fields($export_id);
        $filters = $ee_exports_delta->get_export_filters($export_id);

        $args = [
            'posts_per_page' => 50,
            'post_status' => 'any'
        ];

        $args['post_type'] = $filters->post_types;

        if(!empty($filters->date_filters->from) || !empty($filters->date_filters->to)) {
            $args['date_query'] = [];
            $args['date_query']['inclusive'] = true;


            //Date filter from
            if(!empty($filters->date_filters->from)) {
                $args['date_query']['after'] = $filters->date_filters->from;
            }

            //Date filter to
            if(!empty($filters->date_filters->to)) {
                $args['date_query']['before'] = $filters->date_filters->to;
            }
        }

        // TOOD: Apply tax filters
        if(!empty($filters->taxes)) {
            
        }

        return $args;
    }

    /**
     * @deprecated
     */
    // public function generate_query($export_id, $only_count = false) {
    //     global $ee_exports_delta; 
    //     global $wpdb;

    //     $fields = $ee_exports_delta->get_export_fields($export_id);
    //     $filters = $ee_exports_delta->get_export_filters($export_id);

    //     $query = "SELECT ";

    //     $posts_prefix = count($fields->meta) > 0 ? 'p.' : '';

    //     //First, select all basic fields, as all are part of posts table

    //     if($only_count) {
    //         $query .= 'COUNT(*)';
    //     } else {
    //         foreach($fields->basic as $index => $field) {
    //             $query .= $posts_prefix . $field . ($index !== count($fields->basic) - 1 ?',':'');
    //         }
    //     }
        
    //     if(count($fields->meta) > 0) {
    //         //SUBQUERIES
    //     }

    //     //FROM
    //     $query .= " FROM {$wpdb->posts}";

    //     //default where, so all the conditions can be AND
    //     $query .= ' WHERE 1=1 ';
        
    //     //WHERE [post_type]
    //     $query .= "AND post_type IN(";
        
    //     foreach($filters->post_types as $index => $post_type) {
    //         $query .= "'$post_type'" . ($index != count($filters->post_types) - 1 ? ',': '');
    //     }

    //     $query .= ")";

    //     if(!empty($filters->date_filters->from) || !empty($filters->date_filters->to)) {

    //         //Date filter from
    //         if(!empty($filters->date_filters->from)) {
    //             $query .= 'AND post_date >' . $filters->date_filters->from . ' ';
    //         }

    //         //Date filter to
    //         if(!empty($filters->date_filters->to)) {
    //             $query .= 'AND post_date <' . $filters->date_filters->to;
    //         }
    //     }

    //     //TODO: taxonomy filters

    //     return $query;
    // }

    /**
     * @deprecated
     */
    // public function cron_process_batch_export($export_id, $quantity, $offset) {
    //     //get current export data
    //     require_once 'deltas/class-enhanced-export-delta-exports.php';
        
    //     global $wpdb;
    //     global $ee_exports_delta;
        
    //     $export = $ee_exports_delta->get_export($export_id);

    //     $filename = $this->exports_folder . '/' . $export->file_name;

    //     $query = $this->generate_query($export_id, false);

    //     $query = $query . " LIMIT $quantity OFFSET $offset";

    //     $results = $wpdb->get_results($query, ARRAY_A);

    //     foreach($results as $result)  {
    //         $values = array_values($result);
    //         $values = '"' .implode('","', $values). '"' . PHP_EOL;

    //         $written = $this->save_data($filename, $values);
    //     }

    //     $ee_exports_delta->increase_processed_records($export_id, count($results));

    //     $new_export = $ee_exports_delta->get_export($export_id);
        
    //     if($new_export->processed < $new_export->records && $new_export->status != 'completed') {
    //           wp_schedule_single_event(time()+1, 'process_batch_export', array(
    //             $export_id,
    //             $new_export->records - 50 >= 50 ? 50 : $new_export->records - $new_export->processed,
    //             ($offset + $new_export->records - $new_export->processed),
    //         ));
    //     } else if ($new_export->records == $new_export->processed) {
    //         $ee_exports_delta->set_status($export_id, 'completed');
    //     }

    // }

    public function process_batch_export($export_id) {
        //get current export data
        require_once 'deltas/class-enhanced-export-delta-exports.php';
        
        global $wpdb;
        global $ee_exports_delta;
        
        $export = $ee_exports_delta->get_export($export_id);
        $fields = $ee_exports_delta->get_export_fields($export_id);

        $filename = $this->exports_folder . '/' . $export->file_name;

        $query_args = $this->generate_query_args($export_id);

        $query_args['offset'] = $export->processed;;

        $query = new WP_Query($query_args);

        if($query->have_posts()) {
            foreach($query->posts as $p) {
                $data = [];

                //basic fields
                foreach($fields->basic as $bfield) {
                    $data[] = $this->get_basic_field_value($p, $bfield);
                }

                //taxonomy fields
                foreach($fields->taxes as $tfield) {
                    $data[] = $this->get_taxonomy_field_value($p, $tfield);
                }

                //meta fields - TODO


                //custom fields - TODO
                foreach($fields->custom as $cfield) {
                    $data[] = $this->get_custom_field_value($p, $cfield);
                }
                

                $values = '"' . implode('","', $data). '"' . PHP_EOL;

                $written = $this->save_data($filename, $values);
            }
        } else {
            //export completed
            $ee_exports_delta->set_status($export_id, 'completed');
        }
        
        $qty = count($query->posts);

        $ee_exports_delta->increase_processed_records($export_id, $qty);     
    
    }

    private function get_basic_field_value($post, $field) {
        switch($field) {
            case 'ID':
                    return $post->ID;
                break;
            case 'post_title':
                    return $post->post_title;
                break;
            case 'post_name':
                    return $post->post_name;
                break;
            case 'link':
                    return get_the_permalink($post->ID);
                break;
            case 'post_date':
                    return $post->post_date;
                break;
            case 'posts_status':
                    return $post->post_status;
                break;
            case 'post_excerpt':
                    return str_replace('"', '""', $post->post_excerpt);
                break;
            case 'post_type':
                    return $post->post_type;
                break;
            case 'post_content':
                    return $post->post_content;
                break;
        }
    }

    private function get_taxonomy_field_value($post, $taxonomy) {
        $terms = get_the_terms($post->ID, $taxonomy);

        if($terms) {
            $names = wp_list_pluck($terms, 'name');

            return implode('|', $names);
        }
    }

    private function get_custom_field_value($post, $field_id) {
        global $ee_custom_fields_delta;

        global $wpdb;
        
        $q = $ee_custom_fields_delta->get_custom_field_query($field_id);

        
        $q = str_replace("__post_id__", $post->ID, $q);
        
        $q = str_replace("__db_prefix__", $wpdb->prefix, $q);

        $result = $wpdb->get_var($q);

        return $result;
    }
}
