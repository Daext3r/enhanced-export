<?php

 global $ee_export_id;
 global $ee_exports_delta;
 
 $exporter = new Enhanced_Export_Exporter();

if(is_null($ee_export_id) && !empty($_GET['export_id'])) {
    //resume export
    $ee_export_id = $_GET['export_id'];
    // $ee_exports_delta = new Enhanced_Export_Delta_Exports();
}

$ee_exports_delta->set_status($ee_export_id, 'inprogress');

$export = $ee_exports_delta->get_export($ee_export_id);

$exporter->create_file($ee_export_id);
?>

<section class="ee-wrapper">
    <div class="ee-step">
        <h1>Exportación en progreso...</h1>

        <h3 class="ee-progress-percentage"><?= intval($export->processed / $export->records * 100); ?>%</h3>
        <h5 class="ee-progress-count"><?= $export->processed . '/' . $export->records; ?></h5>
        <div class="ee-progress">
            <div class="ee-progress-bar"></div>
        </div>

        <a style="margin-top: 40px; display: none;" href="<?= WP_CONTENT_URL . '/enhanced-export/exports/' . $export->file_name ?>" class="button button-primary download">Descargar</a>
    </div>
</section>


<script>
    window.addEventListener('load', async function() {
        let running = true;

        do {
            const req = await fetch('/wp-json/enhanced-export/v1/run-export/' + '<?= $ee_export_id; ?>');
            const data = await req.json();

            if(data.status == 'inprogress') {
                const records = data.records;
                const processed = data.processed;

                const progress = parseInt(processed / records * 100);

                document.querySelector('.ee-progress-percentage').textContent = progress + '%';
                document.querySelector('.ee-progress-count').textContent = processed + '/' + records;
                document.querySelector('.ee-progress-bar').style.width = progress + '%';
            } else {
                running = false;
                document.querySelector('.ee-step h1').textContent = 'Export completed!'
                document.querySelector('.download').style.display = 'block';
            }
            
        } while(running);

    })
</script>