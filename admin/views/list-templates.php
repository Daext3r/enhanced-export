<?php 

global $ee_templates_delta;


if (isset($_POST['ee-nonce']) && !empty($_POST['ee-nonce']) && wp_verify_nonce($_POST['ee-nonce'], 'ee-list-nonce')) {
    //Actions
    if (isset($_POST['ee-action']) && !empty($_POST['ee-action']) && $_POST['ee-action'] == 'apply-action') {
        if (
            isset($_POST['ee-templates-action-type']) && !empty($_POST['ee-templates-action-type'])
        ) {
            switch ($_POST['ee-templates-action-type']) {
                case 'delete':
                    if(isset($_POST['ee-template-bulk-edit']) && !empty($_POST['ee-template-bulk-edit'])) {
                        foreach ($_POST['ee-template-bulk-edit'] as $template_id) {
                            $ee_templates_delta->delete_template($template_id);
                        }
                    }
                    break;
                case 'rename':
                    $template_id = $_POST['ee-template-id'];
                    $template_name = $_POST['ee-new-template-name'];

                    $ee_templates_delta->rename_template($template_id, $template_name);
                    break;
            }
        }
    }

}

if(isset($_GET['create-template-from']) && !empty($_GET['create-template-from'])) {
    $from_id = $_GET['create-template-from'];

    $ee_templates_delta->create_template_from_export($from_id);

    wp_safe_redirect('/wp-admin/admin.php?page=enhanced-export-templates');
}

$templates = $ee_templates_delta->list_templates();


?>
<section class="ee-wrapper">
    <form action="" method="POST">
        <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-list-nonce') ?>">

        <div class="ee-templates-actions">

            <select name="ee-templates-action-type">
                <option value="">Selecciona una opción...</option>
                <option value="delete">Eliminar</option>
            </select>

            <button type="submit" class="button button-secondary" name="ee-action" value="apply-action">Aplicar</button>

        </div>


        <div class="ee-templates-table">
            <div class="ee-templates-table__head">
                <div class="ee-templates-table__col-1">
                    <input type="checkbox" id="ee-select-all">
                </div>
                <div class="ee-templates-table__col-1">ID</div>
                <div class="ee-templates-table__col-2">Nombre</div>
                <div class="ee-templates-table__col-2">Fecha</div>
                <div class="ee-templates-table__col-2">Acciones</div>

            </div>
            <div class="ee-templates-table__body">
                <?php foreach ($templates as $template):
                    $template_date = date(get_option('date_format'), strtotime($template->date)) . ", " . date(get_option('time_Format'), strtotime($template->date));
                    ?>
                    <div class="ee-templates-table__template">

                        <div class="ee-templates-table__col-1"><input type="checkbox" class="ee-template-bulk-edit" name="ee-template-bulk-edit[]"
                                value="<?= $template->id; ?>">
                        </div>
                        <div class="ee-templates-table__col-1">
                            <?= $template->id; ?>
                        </div>
                        <div class="ee-templates-table__col-2">
                            <?= $template->name; ?>
                        </div>
                        <div class="ee-templates-table__col-2">
                            <?= $template_date; ?>
                        </div>

                        <div class="ee-templates-table__col-2">
                            <button class="ee-rename button button-primary" data-template-id="<?= $template->id; ?>" data-template-name="<?= $template->name; ?>">Renombrar</button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if(count($templates) == 0): ?>
                    <div class="ee-templates-table__template" style="text-align: center; width:100%; display: block;">No se han encontrado plantillas</div>  
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="ee-templates-pagination">
        <?php
        // // URL base para los enlaces de paginación
        // $urlBase = 'admin.php?page=enhanced-template-templates';

        // $pages_count = $ee_templates_delta->get_template_pages_count();

        // // Generar los enlaces de paginación
        // for ($i = 1; $i <= $pages_count; $i++) {
        //     // Agregar la clase 'active' si la página coincide con la página actual
        //     $claseActiva = ($paged == $i) ? 'active' : '';

        //     // Construir el enlace
        //     echo "<a href='$urlBase&paged=$i' class='$claseActiva'>$i</a>";
        // }
        ?>
    </div>
</section>


<section class="ee-popup" style="display: none">
    <form class="ee-popup-content" method="POST">
        <input type="hidden" name="ee-nonce" value="<?= wp_create_nonce('ee-list-nonce') ?>">
        
        <span class="ee-popup-close">&times;</span>
        <h2>Escribe un nuevo nombre para la plantilla con ID <span></span></h2>
        <input type="text" name="ee-new-template-name" id="" placeholder="Nuevo nombre de plantilla">
        <input type="hidden" name="ee-template-id">
        <input type="hidden" name="ee-templates-action-type" value="rename">

        <button type="submit" class="button button-secondary" name="ee-action" value="apply-action">Aplicar</button>

    </form>
</section>

<script>
    window.addEventListener('load', function() {
        document.querySelector('#ee-select-all').addEventListener('click', function() {
          document.querySelectorAll('.ee-template-bulk-edit').forEach(check => {
                check.checked = this.checked;
            })
        })

        document.querySelectorAll('.ee-rename').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.ee-popup').style.display = 'flex';
                document.querySelector('.ee-popup h2 span').innerHTML = this.dataset.templateId;
                document.querySelector('.ee-popup input[name="ee-new-template-name"]').value = this.dataset.templateName;
                document.querySelector('.ee-popup input[name="ee-template-id"]').value = this.dataset.templateId;
            })
        })

        document.querySelector('.ee-popup-close').addEventListener('click', function() {
            document.querySelector('.ee-popup').style.display = 'none';
        })
    })
</script>

<style>
    .ee-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .ee-popup-content h2 {
        margin: 0;
    }

    .ee-popup-content {
        background: #fff;
        width: 600px;
        height: auto;
        padding: 30px;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 20px;
        justify-content: center;
        align-items: center;
        align-content: center;
    
    }

    .ee-popup-content > *:not(.ee-popup-close) {
        width: 100%;
        text-align:center;
    }

    .ee-popup-close {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer; 
        font-size: 30px;
    }
    
    .ee-templates-table {
        display: flex;
        flex-direction: column;
        background: #fff;

        border: 1px solid #c3c4c7;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .ee-templates-table__head {
        border-bottom: 1px solid #c3c4c7;
        padding: 12px;

        display: flex;
        flex-wrap: nowrap;
    }

    .ee-templates-table__template {
        display: flex;
        flex-wrap: nowrap;
        padding: 8px 10px;
    }

    .ee-templates-table__template:nth-child(odd) {
        background: #f6f7f7
    }

    .ee-templates-table__col-1 {
        flex: 1;
    }

    .ee-templates-table__col-2 {
        flex: 2;
    }

    .ee-templates-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: stretch;
    }
</style>
