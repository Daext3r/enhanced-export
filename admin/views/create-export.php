<?php
    global $ee_exports_delta;

    global $ee_export_id;
    $ee_export_id = -1;

    $ee_step = isset($_POST['ee-step']) ? $_POST['ee-step'] : 'create-export';

    //from the 2nd step, reads the export id
    if(isset( $_POST['ee-export-id']) && isset($_POST['ee-step-from'])) {
        $ee_export_id = $_POST['ee-export-id'];
    } else if (isset( $_GET['ee-export-id'])){
        $ee_export_id = $_GET['ee-export-id'];
    }
    
    $ee_error = false;

    require_once plugin_dir_path(dirname(__FILE__,2)) .'includes/class-enhanced-export-exporter.php';

    //if this is the second step or more
    if(isset($_POST['ee-step-from'])) {
        //validates nonce
        if(isset($_POST['ee-nonce']) && !empty($_POST['ee-nonce']) && wp_verify_nonce($_POST['ee-nonce'], 'ee-export-nonce')) {
            
            //do something depending to the step the user came from
            switch($_POST['ee-step-from']) {
                case 'create-export':
                    //create the export
                    //validates there's an export name
                    $export_name = isset($_POST['ee-export-name']) ? $_POST['ee-export-name'] :'';
                    $export_create_result = $ee_exports_delta->create_export($export_name);

                    if($export_create_result->success) {
                        $ee_export_id = $export_create_result->response->insert_id;
                        
                        if(isset($_POST['ee-from-template']) && !empty($_POST['ee-from-template'])) {
                            $ee_exports_delta->apply_template_to_export($ee_export_id, $_POST['ee-from-template']);
                        }
                    } else {
                        $ee_error = $export_create_result;

                        //prevent going to the next step
                        $ee_step = $_POST['ee-step-from'];
                    }
                    break;

                case 'select-post-types':
                    //select post types
                    $export_post_types = isset($_POST['ee-post-types']) ? $_POST['ee-post-types'] : [];
                    $export_post_types_result = $ee_exports_delta->set_export_post_types($ee_export_id, $export_post_types);

                    if(!$export_post_types_result->success) {
                        $ee_error = $export_post_types_result;

                        //prevent going to the next step
                        $ee_step = $_POST['ee-step-from'];
                    } 
                    break;

                case 'apply-filters':
                    // apply filters
                    $export_date_from = isset($_POST['ee-date-from']) ? $_POST['ee-date-from'] : '';
                    $export_date_to = isset($_POST['ee-date-to']) ? $_POST['ee-date-to'] : '';
                    
                    $export_dates_result = $ee_exports_delta->set_export_date_filter($ee_export_id, $export_date_from, $export_date_to);

                    if(!$export_dates_result->success) {
                        $ee_error = $export_dates_result;

                        //prevent going to the next step
                        $ee_step = $_POST['ee-step-from'];
                    } 
                    break;

                case 'select-fields':
                    $export_fields_basic = isset($_POST['ee-basic-fields']) ? $_POST['ee-basic-fields'] : [];
                    $export_fields_taxes = isset($_POST['ee-taxonomy-fields']) ? $_POST['ee-taxonomy-fields'] : [];
                    $export_fields_meta = isset($_POST['ee-meta-fields']) ? $_POST['ee-meta-fields'] : [];
                    $export_fields_custom = isset($_POST['ee-custom-fields']) ? $_POST['ee-custom-fields'] : [];

                    $export_fields_result_basic = $ee_exports_delta->set_export_basic_fields($ee_export_id, $export_fields_basic);
                    $export_fields_result_taxes = $ee_exports_delta->set_export_taxes_fields($ee_export_id, $export_fields_taxes);
                    $export_fields_result_meta = $ee_exports_delta->set_export_meta_fields($ee_export_id, $export_fields_meta);
                    $export_fields_result_custom = $ee_exports_delta->set_export_custom_fields($ee_export_id, $export_fields_custom);

                    if($export_fields_result_basic->success && 
                    $export_fields_result_taxes->success && 
                    $export_fields_result_meta->success && 
                    $export_fields_result_custom->success) {
                        //sets the export to ready
                        $ee_exports_delta->set_status($ee_export_id, 'ready');
                        $ee_exports_delta->calculate_initial_records($ee_export_id);
                    } else {
                        $ee_error = $export_fields_result;

                        //prevent going to the next step
                        $ee_step = $_POST['ee-step-from'];
                    }


                    break;

                case 'confirm-settings':
                   

                    // error_log("should be scheduled");

                    break;

            }
            
        }

    }


    if($ee_error !== false):
?>

<section class="ee-error">
    <p>
        <?= $ee_error->response; ?>
    </p>
</section>

<?php endif; ?>



<section class="ee-wrapper">
    <?php
    
    //import the next step's template
        if(file_exists(plugin_dir_path(__DIR__) . "views/partials/$ee_step.php")) {
            include_once plugin_dir_path(__DIR__) . "views/partials/$ee_step.php";
        }
    ?>
</section>