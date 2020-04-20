<?php
/**
 * @package CarabusPlugin
 */
?>

<div class="wrap">
    <h1><?= __('Carabus Plugin admin area', 'carabus') ?></h1>
    <form action='options.php' method='post'>

        <?php
        settings_fields( $this->get_setting('slug') );
        do_settings_sections( $this->get_setting('slug') );
        submit_button();
        ?>

    </form>
</div>