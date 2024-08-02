<?php

    global $ee_exports_delta;
    global $ee_custom_fields_delta;
    global $ee_export_id;


    $basic_fields = [
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

    $custom_fields = $ee_custom_fields_delta->list_custom_fields();

    $taxonomies = get_taxonomies([], 'objects');

    $selected_fields = $ee_exports_delta->get_export_fields($ee_export_id);
    $selected_filters = $ee_exports_delta->get_export_filters($ee_export_id);
?>

<form action="" method="POST" class="ee-step">
    <h2 class="ee-section-title">Confirma los ajustes de exportación</h2>
    <input type="hidden" name="ee-step-from" value="confirm-settings">
    <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-export-nonce'); ?>">
    <input type="hidden" name="ee-export-id" value="<?= $ee_export_id; ?>">

    <h4>La exportación comenzará en el siguiente paso. Por favor, confirma los siguientes ajustes o vuelve a un paso anterior:</h4>
    <div class="ee-columns">
        <div class="ee-column">
            <h3>Campos básicos</h3>
            
            <div class="ee-selector ee-selector-multiple">
                <?php foreach($selected_fields->basic as $bfield): ?>
                    <div class="ee-selector-item">
                            <label>
                                <?= $basic_fields[$bfield]; ?>
                            </label>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>        

        <div class="ee-column">
            <h3>Taxonomías</h3>
            
            <div class="ee-selector ee-selector-multiple">
                <?php foreach($selected_fields->taxes as $tax): 
                        $taxonomy = get_taxonomy($tax);
                    ?>
                    <div class="ee-selector-item">
                            <label>
                                <?= $taxonomy->labels->name ?>
                            </label>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>        

        <div class="ee-column">
            <h3>Filtros</h3>
            
            <h5>Tipos de contenido</h5>
            <div class="ee-selector ee-selector-multiple">
                <?php foreach($selected_filters->post_types as $post_type): 
                        $post_type = get_post_type_object($post_type);
                    ?>
                    <div class="ee-selector-item">
                            <label>
                                <?= $post_type->label; ?>
                            </label>
                        </div>
                <?php endforeach; ?>
            </div>

            <h5>Filtro de fechas</h5>

            <?php
                $filter_date_from = date(get_option('date_format'), strtotime($selected_filters->date_filters->from));
                $filter_date_to = date(get_option('date_format'), strtotime($selected_filters->date_filters->to));
            ?>
            <div class="ee-selector ee-selector-multiple">
                <?php if(!empty($selected_filters->date_filters->from)): ?>
                    <div class="ee-selector-item">
                        <label>
                            Desde <?= $filter_date_from; ?>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if(!empty($selected_filters->date_filters->to)): ?>
                    <div class="ee-selector-item">
                        <label>
                            Hasta <?= $filter_date_to; ?>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if(empty($selected_filters->date_filters->from) && empty($selected_filters->date_filters->to)): ?>
                    <div class="ee-selector-item">
                        <label>
                            No hay filtros de fecha aplicados
                        </label>
                    </div>
                <?php endif; ?>

            </div>
        </div> 

        <div class="ee-column">
            <h3>Campos personalizados</h3>
            
            <div class="ee-selector ee-selector-multiple">
                <?php foreach($selected_fields->custom as $cfield): ?>
                    <div class="ee-selector-item">
                            <label>
                                <?= $custom_fields[$cfield]; ?>
                            </label>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="ee-buttons">
        <button class="button button-primary ee-prev-step" name="ee-step" value="select-fields">Paso anterior</button>
        <button class="button button-primary ee-next-step" name="ee-step" value="run-export">Paso siguiente</button>
    </div>
</form>

<style>
    h5, h3 {
        margin: 0;
        width: 100%;
    }
    .ee-selector {
        padding: 10px!important;
    }
</style>