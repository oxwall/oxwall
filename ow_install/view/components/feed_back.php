<?php if (!empty($_assign_vars['msgs'])) { ?>
<div class="install_feedback">
    <?php foreach ($_assign_vars['msgs'] as $msg) { ?>
        <div class="feedback_msg <?php echo $msg['type'] ?>">
            <?php echo $msg['message'] ?>
        </div>    
    <?php } ?>
</div>
<?php } ?>