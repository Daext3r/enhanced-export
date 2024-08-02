
<section class="ee-wrapper">
    <?php
    
    //import the next step's template
        if(file_exists(plugin_dir_path(__DIR__) . "views/partials/run-export.php")) {
            include_once plugin_dir_path(__DIR__) . "views/partials/run-export.php";
        } 
    ?>
</section>