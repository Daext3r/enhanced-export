<?php

global $ee_export_id;

$taxonomies = get_taxonomies([], 'objects');

?>

<form action="" method="POST" class="ee-step">
    <h2 class="ee-section-title">Aplica filtros a tu búsqueda (opcional)</h2>

    <input type="hidden" name="ee-step-from" value="apply-filters">
    <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-export-nonce'); ?>">
    <input type="hidden" name="ee-export-id" value="<?= $ee_export_id; ?>">

    <div class="ee-columns">
        <div class="ee-column">
            <h4 class="ee-section-subtitle">Filtro de fechas</h4>

            <div class="ee-input-wrapper">
                <label for="ee-date-from">Desde:</label>
                <input type="date" name="ee-date-from" id="ee-date-from">
                <span class="ee-clear-field">&times;</span>
            </div>

            <div class="ee-input-wrapper">
                <label for="ee-date-to">Hasta:</label>
                <input type="date" name="ee-date-to" id="ee-date-to">
                <span class="ee-clear-field">&times;</span>
            </div>
        </div>

        
    
        <div class="ee-column">
        
        </div>
        
       
    </div>

    <div class="ee-buttons">
        <button class="button button-primary ee-prev-step" name="ee-step" value="select-post-type">Paso anterior</button>
        <button class="button button-primary ee-next-step" name="ee-step" value="select-fields">Paso siguiente</button>
    </div>
</form>