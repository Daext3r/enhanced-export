<?php

 global $ee_export_id;
 global $ee_exports_delta;
 
 $exporter = new Enhanced_Export_Exporter();

if(is_null($ee_export_id) && !empty($_GET['export_id'])) {
    //resume export
    $ee_export_id = $_GET['export_id'];
    // $ee_exports_delta = new Enhanced_Export_Delta_Exports();

    if(wp_next_scheduled('process_batch_export')) {
        do_action('process_batch_export', $ee_export_id, 20, 0);
    }

} else {
    //normal page load
    //creates the file if not exists
    $exporter->create_file($ee_export_id);

    //if theres an export running
    if(wp_next_scheduled('process_batch_export')) {
        $ee_exports_delta->set_status($ee_export_id, 'ready');
    } else {
        //if no export running, start
        $ee_exports_delta->set_status($ee_export_id, 'inprogress');
        do_action('process_batch_export', $ee_export_id, 20, 0);
    }

}

$export = $ee_exports_delta->get_export($ee_export_id);

?>

<section class="ee-wrapper">
    <div class="ee-step">
        <h1>Export in progress...</h1>

        <h3 class="ee-progress-percentage"><?= intval($export->processed / $export->records * 100); ?>%</h3>

        <div class="ee-progress">
            <div class="ee-progress-bar"></div>
        </div>

        <a style="margin-top: 40px;" href="<?= WP_CONTENT_URL . '/enhanced-export/exports/' . $export->file_name ?>" class="button button-primary download">Download anyway</a>
    </div>
</section>


<script>
    window.addEventListener('load', async function() {
        async function checkStatus() {
            await fetch('/wp-json/enhanced-export/v1/export-status/' + '<?= $ee_export_id; ?>').then(res => res.json()).then(data => {
                if(data.status == 'inprogress') {
                    const records = data.records;
                    const processed = data.processed;

                    const progress = parseInt(processed / records * 100);
                    lastProgress = processed;

                    document.querySelector('.ee-progress-percentage').textContent = progress + '%';
                    document.querySelector('.ee-progress-bar').style.width = progress + '%';

                    setTimeout(() => {
                        checkStatus()
                    }, 1000);
                } 
            });

            fetch('/wp-json/enhanced-export/v1/resume-export/' + '<?= $ee_export_id; ?>').then(res => res.json()).then(data => {});
        }

        checkStatus();

        document.querySelector('.download').addEventListener('click', function(e) {
            e.preventDefault();

            const confirmResult = confirm('File will be downloaded even if the export is not completed.');

            if(confirmResult) {
                window.open(this.href, '_blank');
            }
        })
    })
</script>