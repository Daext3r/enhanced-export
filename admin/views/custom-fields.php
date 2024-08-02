<?php 

global $ee_custom_fields_delta;

//save the fields

if(isset($_POST["ee-custom-fields-nonce"]) && $_POST["ee-custom-fields-nonce"] != "" && wp_verify_nonce($_POST['ee-custom-fields-nonce'], 'ee-cf-nonce')) {
    //nonce is valid
    $result = $ee_custom_fields_delta->save_custom_fields($_POST['ee-custom-fields']);
}

$custom_fields = $ee_custom_fields_delta->get_custom_fields();

$fields_name = [];
$fields_data = [];

foreach($custom_fields as $cf) {
    $fields_data = [
        "name" => $cf->name,
        "id" => $cf->id,
        "query" => $cf->query
    ];
}

$first_button = true;
$first_field = true;
?>

<form action="" method="POST" class="ee-custom-fields-form">
    <input type="hidden" name="ee-custom-fields-nonce" value="<?= wp_create_nonce('ee-cf-nonce'); ?>">
     <div class="col ee-custom-fields-col">
        <?php foreach($custom_fields as $index => $data): ?>
            <button class="ee-button ee-custom-field-button <?= $first_button ? 'active' : ''; ?>" data-field-index="<?= $index; ?>" >
                <?= $data->name; ?>
            </button>
        <?php 
        $first_button = false;
    endforeach; ?>

        <button class="ee-button ee-add-field">+ Añadir campo personalizado</button>
        <button type="submit" class="ee-save-fields button button-primary">Guardar campos personalizados</button>
     </div>

     <div class="col ee-custom-fields-col ee-custom-fields-list">
        <p>Variables:</p>
        <ul>
            <li><b>__post_id__</b>: ID del post</li>
            <li><b>__db_prefix__</b>: Prefijo de BBDD</li>
        </ul>
        <?php foreach($custom_fields as $index => $data): ?>
            <div class="ee-custom-field <?= $first_field ? 'active' : ''; ?>" data-field-index="<?= $index; ?>">
                <input type="hidden" name="ee-custom-fields[<?= $index; ?>][id]" value="<?= $data->id; ?>">
                <input required type="text" placeholder="Field name" name="ee-custom-fields[<?= $index; ?>][name]" value="<?= $data->name; ?>">
                <textarea required name="ee-custom-fields[<?= $index; ?>][query]" rows="10"><?= str_replace('\\', '', $data->query); ?></textarea>
            </div>
        <?php 
        $first_field = false;
        endforeach; ?>
     </div>
</form>


<style>
    .ee-custom-fields-form {
        display: flex;
        flex-direction: row;
        background: #fff;
        width: calc(100% - 20px);
        border-radius: 5px;
        border: 1px solid #cecece;
        height: 90vh;
        margin-top: 30px;
        padding: 20px;
    }

    .ee-custom-fields-col {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 50%;
        overflow: auto;
        max-height: calc(100% - 40px);
    }

    .ee-custom-fields-col:first-child {
        padding-bottom: 50px;
        border-right: 1px solid #cecece;
        padding-right: 30px;
    }

    .ee-save-fields {
        width: calc(100% - 30px);
        position: sticky;
        width: 100%;
        bottom: 0;
        z-index: 999;
        border-radius: 0px!important;
    }

    .ee-button {
        width: 100%;
        border: 1px solid #cecece;
        background: #fff;
        border-radius: 1px;
        padding: 10px 60px;
        cursor: pointer;
    }
    
    .ee-custom-fields-list {
        padding-left: 20px;
    }

    .ee-custom-fields-list :is(p, ul){
        margin: 0;
    }

    .ee-custom-field {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .ee-custom-field:not(.active) {
        display: none
    }
</style>

<script>
    window.addEventListener('load', function() {
        const buttonsWrapper = document.querySelector('.ee-custom-fields-col');
        document.querySelector('.ee-add-field').addEventListener('click', function(e) {
            e.preventDefault();

            const lastButton = Array.from(document.querySelectorAll('.ee-custom-field-button')).reverse()[0];

            const id = lastButton ? parseInt(lastButton.dataset.fieldIndex) + 1 : 0;

            //creates the button
            const button = document.createElement('button');
            button.textContent = 'Unnamed field';
            button.classList.add('ee-button');
            button.classList.add('ee-custom-field-button');

            //adds the data
            button.dataset.fieldIndex = id;

            //adds the event listener and adds the button to the dom
            button.addEventListener('click', customFieldButtonCallback);

            buttonsWrapper.insertBefore(button, buttonsWrapper.children[0]);

            //creates the inputs 

            const div = document.createElement('div');
            div.classList.add('ee-custom-field');
            div.dataset.fieldIndex = id;

            div.innerHTML = `
                <input type="hidden" name="ee-custom-fields[${id}][id]">
                <input type="text" placeholder="Field name" name="ee-custom-fields[${id}][name]" data-field-index="${id}" oninput="customFieldInputNameCallback(this)" required>
                <textarea required name="ee-custom-fields[${id}][query]" rows="10"></textarea>
            `;

            document.querySelector('.ee-custom-fields-list').appendChild(div);

            setTimeout(() => {
                button.click();
            }, 200)

        });

        document.querySelectorAll('.ee-custom-field-button').forEach(button => {
            button.addEventListener('click', customFieldButtonCallback);
        });

        function customFieldButtonCallback(event) {
            event.preventDefault();

            //adds the class active to the button and removes it from the current
            document.querySelector('.ee-custom-field-button.active') && document.querySelector('.ee-custom-field-button.active').classList.remove('active')
            this.classList.add('active');

            //hides the field wrapper  
            document.querySelector('.ee-custom-field.active') && document.querySelector('.ee-custom-field.active').classList.remove('active');

            //console.log(document.querySelector(`.ee-custom-field[data-field-index="${this.dataset.fieldIndex}"]`))
            document.querySelector(`.ee-custom-field[data-field-index="${this.dataset.fieldIndex}"]`).classList.add('active')
        }



        document.querySelector('.ee-custom-field-button') && document.querySelector('.ee-custom-field-button').click();
    });

    function customFieldInputNameCallback(input) {
            const value = input.value;

            const fieldIndex = input.dataset.fieldIndex;

            document.querySelector(`.ee-custom-field-button[data-field-index="${fieldIndex}"]`).textContent = value;

        }
</script>