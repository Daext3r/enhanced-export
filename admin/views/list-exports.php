<?php

require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/deltas/class-enhanced-export-delta-exports.php';

global $ee_exports_delta;

$export_statuses = [
    'ready' => 'Ready',
    'inprogress' => 'In progress',
    'completed' => 'Completed',
    'toconfigure' => 'To configure'
];

$ee_order = 'desc';
$ee_orderby = 'id';

if (isset($_POST['ee-nonce']) && !empty($_POST['ee-nonce']) && wp_verify_nonce($_POST['ee-nonce'], 'ee-list-nonce')) {
    //Actions
    if (isset($_POST['ee-action']) && !empty($_POST['ee-action']) && $_POST['ee-action'] == 'apply-action') {
        if (
            isset($_POST['ee-exports-action-type']) && !empty($_POST['ee-exports-action-type']) &&
            isset($_POST['ee-export-bulk-edit']) && !empty($_POST['ee-export-bulk-edit'])
        ) {
            switch ($_POST['ee-exports-action-type']) {
                case 'delete':
                    foreach ($_POST['ee-export-bulk-edit'] as $export_id) {
                        $ee_exports_delta->delete_export($export_id);
                    }
                    break;
            }
        }
    }

    //Filters
    if (isset($_POST['ee-action']) && !empty($_POST['ee-action']) && $_POST['ee-action'] == 'apply-filters') {
        if (
            isset($_POST['ee-exports-order']) && !empty($_POST['ee-exports-order']) &&
            isset($_POST['ee-exports-orderby']) && !empty($_POST['ee-exports-orderby'])
        ) {
            $ee_order = $_POST['ee-exports-order'];
            $ee_orderby = $_POST['ee-exports-orderby'];
        }
    }
}

$paged = isset($_GET['paged']) ? $_GET['paged'] : '';

if (empty($paged)) {
    $paged = 1;
}

$exports = $ee_exports_delta->list_exports($paged, $ee_orderby, $ee_order);
?>

<section class="ee-wrapper">
    <form action="" method="POST">
        <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-list-nonce') ?>">

        <div class="ee-exports-actions">

            <select name="ee-exports-action-type">
                <option value="">Selecciona una opción...</option>
                <option value="delete">Eliminar</option>
            </select>

            <button type="submit" class="button button-secondary" name="ee-action" value="apply-action">Aplicar</button>

            <div class="ee-separator"></div>

            <select name="ee-exports-orderby" id="">
                <option value="">Selecciona un campo para ordenar</option>
                <option value="id" <?= $ee_orderby == 'id' ? 'selected' : ''; ?>>ID</option>
                <option value="name" <?= $ee_orderby == 'name' ? 'selected' : ''; ?>>Nombre</option>
            </select>

            <select name="ee-exports-order" id="">
                <option value="">Selecciona un orden</option>
                <option value="desc" <?= $ee_order == 'desc' ? 'selected' : ''; ?>>Descendente</option>
                <option value="asc" <?= $ee_order == 'asc' ? 'selected' : ''; ?>>Ascendente</option>
            </select>

            <button type="submit" class="button button-secondary" name="ee-action" value="apply-filters">Filtrar</button>

        </div>


        <div class="ee-exports-table">
            <div class="ee-exports-table__head">
                <div class="ee-exports-table__col-1">
                    <input type="checkbox" id="ee-select-all">
                </div>
                <div class="ee-exports-table__col-1">ID</div>
                <div class="ee-exports-table__col-2">Nombre</div>
                <div class="ee-exports-table__col-2">Fecha</div>
                <div class="ee-exports-table__col-1">Registros</div>
                <div class="ee-exports-table__col-1">Procesado</div>
                <div class="ee-exports-table__col-1">Estado</div>
                <div class="ee-exports-table__col-2">Acciones</div>

            </div>
            <div class="ee-exports-table__body">
                <?php foreach ($exports as $export):
                    $export_date = date(get_option('date_format'), strtotime($export->date)) . ", " . date(get_option('time_Format'), strtotime($export->date));
                    ?>
                    <div class="ee-exports-table__export">
                        <div class="ee-exports-table__col-1"><input type="checkbox" class="ee-export-bulk-edit" name="ee-export-bulk-edit[]"
                                value="<?= $export->id; ?>"></div>
                        <div class="ee-exports-table__col-1">
                            <?= $export->id; ?>
                        </div>
                        <div class="ee-exports-table__col-2">
                            <?= $export->name; ?>
                        </div>
                        <div class="ee-exports-table__col-2">
                            <?= $export_date; ?>
                        </div>
                        <div class="ee-exports-table__col-1">
                            <?= $export->records; ?>
                        </div>
                        <div class="ee-exports-table__col-1">
                            <?= $export->processed; ?>
                        </div>
                        <div class="ee-exports-table__col-1">
                            <?= $export_statuses[$export->status]; ?>
                        </div>

                        <div class="ee-exports-table__col-2">
                            <?php if($export->status == 'completed'): ?>
                                <a target="_blank" href="<?= WP_CONTENT_URL . '/enhanced-export/exports/' . $export->file_name ?>" class="button button-primary">Descargar</a>
                                <a href="/wp-admin/admin.php?page=enhanced-export-templates&create-template-from=<?= $export->id; ?>" class="button button-primary">Crear plantilla</a>
                            <?php endif; ?>    
                        
                            
                            <?php if($export->status == 'inprogress' || $export->status == 'ready'): ?>
                                <a href="/wp-admin/admin.php?page=enhanced-export-run-export&export_id=<?= $export->id; ?>" class="button button-primary">Continuar exportación</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if(count($exports) == 0): ?>
                    <div class="ee-exports-table__export" style="text-align: center; width:100%; display: block;">No se han encontrado exportaciones</div>  
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="ee-exports-pagination">
        <?php
        // URL base para los enlaces de paginación
        $urlBase = 'admin.php?page=enhanced-export-exports';

        $pages_count = $ee_exports_delta->get_export_pages_count();

        // Generar los enlaces de paginación
        for ($i = 1; $i <= $pages_count; $i++) {
            // Agregar la clase 'active' si la página coincide con la página actual
            $claseActiva = ($paged == $i) ? 'active' : '';

            // Construir el enlace
            echo "<a href='$urlBase&paged=$i' class='$claseActiva'>$i</a>";
        }


        ?>
    </div>
</section>

<style>
    .ee-exports-table {
        display: flex;
        flex-direction: column;
        background: #fff;

        border: 1px solid #c3c4c7;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .ee-exports-table__head {
        border-bottom: 1px solid #c3c4c7;
        padding: 12px;

        display: flex;
        flex-wrap: nowrap;
    }

    .ee-exports-table__export {
        display: flex;
        flex-wrap: nowrap;
        padding: 8px 10px;
    }

    .ee-exports-table__export:nth-child(odd) {
        background: #f6f7f7
    }

    .ee-exports-table__col-1 {
        flex: 1;
    }

    .ee-exports-table__col-2 {
        flex: 2;
    }

    .ee-exports-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: stretch;
    }

    .ee-separator {

        width: 10px;
    }
</style>

<script>
    document.querySelector('#ee-select-all').addEventListener('click', function() {
        document.querySelectorAll('.ee-export-bulk-edit').forEach(check => {
            check.checked = this.checked;
        })
    })
</script>