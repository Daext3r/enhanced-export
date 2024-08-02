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

?>

<form action="" method="POST" class="ee-step">
    <h2 class="ee-section-title">Selecciona los campos a exportar</h2>
    <input type="hidden" name="ee-step-from" value="select-fields">
    <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-export-nonce'); ?>">
    <input type="hidden" name="ee-export-id" value="<?= $ee_export_id; ?>">

    <div class="ee-columns">
        <div class="ee-column">
            <h4 class="ee-section-subtitle">Campos básicos</h4>
            <div class="ee-selector ee-selector-multiple">
                <?php foreach ($basic_fields as $basic_field_key => $basic_field_label): ?>

                    <div class="ee-selector-item">
                        <input type="checkbox" name="ee-basic-fields[]" id="ee-basic-field-<?= $basic_field_key; ?>"
                            value="<?= $basic_field_key; ?>" <?= in_array($basic_field_key, $selected_fields->basic) ? 'checked' : ''; ?>>
                        <label for="ee-basic-field-<?= $basic_field_key; ?>">
                            <?= $basic_field_label; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4 class="ee-section-subtitle">Campos personalizados</h4>

            <div class="ee-selector ee-selector-multiple">
                <?php foreach ($custom_fields as $custom_field_id => $custom_field_label): ?>
                    <div class="ee-selector-item">
                        <input type="checkbox" name="ee-custom-fields[]" id="ee-custom-field-<?= $custom_field_id; ?>"
                            value="<?= $custom_field_id; ?>" <?= in_array($custom_field_id, $selected_fields->custom) ? 'checked' : ''; ?>>
                        <label for="ee-custom-field-<?= $custom_field_id; ?>">
                            <?= $custom_field_label; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <?php if(count($custom_fields) == 0): ?>
                    <p>No hay campos personalizados</p>
                <?php endif; ?>
            </div>
        </div>

        
    
        <div class="ee-column">
            <h4 class="ee-section-subtitle">Taxonomías</h4>
            
            <div class="ee-selector ee-selector-multiple">
                <?php foreach ($taxonomies as $taxonomy):
                    if(in_array($taxonomy->name, ['nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area', 'wp_pattern_category'])) continue;
                        $taxonomy_slug = $taxonomy->name;
                        $taxonomy_label = $taxonomy->label;
                    ?>

                    <div class="ee-selector-item">
                        <input type="checkbox" name="ee-taxonomy-fields[]" id="ee-taxonomy-field-<?= $taxonomy_slug; ?>"
                            value="<?= $taxonomy_slug; ?>" <?= in_array($taxonomy_slug, $selected_fields->taxes) ? 'checked' : ''; ?>>
                        <label for="ee-taxonomy-field-<?= $taxonomy_slug; ?>">
                            <?= $taxonomy_label; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <?php if(count($taxonomies) == 0): ?>
                    <p>No hay taxonomías</p>
                <?php endif; ?>
            </div>
        </div>
        
        
    </div>

    

    <div class="ee-buttons">
        <button class="button button-primary ee-prev-step" name="ee-step" value="apply-filters" >Paso anterior</button>
        <button class="button button-primary ee-next-step" name="ee-step" value="confirm-settings">Paso siguiente</button>
    </div>
</form>