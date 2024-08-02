<?php


$registered_post_types = get_post_types([
    'public' => true,
    '_builtin' => false,
], 'object');

$post_types = ['post' => 'Post', 'page' => 'Page'];

foreach ($registered_post_types as $post_type) {
    $post_types[$post_type->name] = $post_type->label;
}

global $ee_export_id;
global $ee_exports_delta;

$selected_post_types = $ee_exports_delta->get_export_post_types($ee_export_id);

?>

<form action="" method="POST" class="ee-step">
    <h2 class="ee-section-title">Selecciona los tipo(s) de contenido a exportar</h2>
    <input type="hidden" name="ee-step-from" value="select-post-types">
    <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-export-nonce'); ?>">
    <input type="hidden" name="ee-export-id" value="<?= $ee_export_id; ?>">

    <div class="ee-selector ee-selector-multiple">
        <?php foreach ($post_types as $post_type_key => $post_type_label): ?>

            <div class="ee-selector-item">
                <input type="checkbox" class="ee-export-types" name="ee-post-types[]" id="ee-post-type-<?= $post_type_key; ?>"
                    value="<?= $post_type_key; ?>" <?= in_array($post_type_key, $selected_post_types) ? 'checked' : ''; ?>>
                <label for="ee-post-type-<?= $post_type_key; ?>">
                    <?= $post_type_label; ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ee-buttons">
        <button class="button button-primary ee-prev-step" name="ee-step" value="create-export" >Paso anterior</button>
        <button class="button button-primary ee-next-step" name="ee-step" value="apply-filters">Paso siguiente</button>
    </div>
</form>