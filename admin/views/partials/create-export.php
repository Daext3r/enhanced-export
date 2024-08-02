<?php
    //creates a default export name
    $export_name = 'export_' . date('Ymd_His');

    //gets all templates

    global $ee_templates_delta;

    $templates = $ee_templates_delta->list_templates();
?>



<form method="POST" action="/wp-admin/admin.php?page=enhanced-export" class="ee-step">
    <h2 class="ee-section-title">Crear nueva exportación</h2>

    <input type="hidden" name="ee-step-from" value="create-export">
    <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-export-nonce'); ?>">

    <input type="text" name="ee-export-name" id="ee-export-name" class="ee-input" value="<?= $export_name; ?>">

    <h3 style="margin-top: 40px;">¿Quieres empezar desde una plantilla?</h3>
    <select name="ee-from-template" <?= count($templates) == 0 ? 'disabled' : ''; ?>>
        <?php if(count($templates) > 0): ?>
            <option value="" selected>Sin plantilla</option>
            <?php foreach ($templates as $template): ?>
                <option value="<?= $template->id; ?>"><?= $template->name; ?></option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">No hay plantillas</option>
        <?php endif; ?>
    </select>

    <div class="ee-buttons">
        <button type="submit" name="ee-step" value="select-post-types" class="button button-primary ee-next-step">Paso siguiente</button>
    </div>
</form>