<p style=" font-size: 16px; font-family:Arial; color: #626262; margin: -42px 0 18px 0; border-bottom: 1px solid #e9eaeb; padding-bottom: 20px;" > Finalizing install </p>
<?php echo install_tpl_feedback(); ?>

<?php
if ( $_assign_vars['dirs'] )
{
?>
<div class="feedback_msg error">
	&bull; You need to set recursive "write" permissions for these folders: (<a target="_blank" href="http://docs.oxwall.org/install:index#writable-folders "> <b>?</b></a>)
</div>

<ul class="directories">
    <?php foreach ($_assign_vars['dirs'] as $dir) { ?>
	    <li><?php echo $dir; ?></li>
	<?php } ?>
</ul>

<hr />
<?php
}
?>
<form method="post">
    <div style="<?= $_assign_vars['isConfigWritable'] ? 'display: none;' : ''; ?>" >
        <p>&bull; Please copy and paste this code replacing the existing one into <b>ow_includes/config.php</b> file.<br />Make sure you do not have any whitespace before and after the code.</p>
        <textarea rows="5" name="configContent" class="config" style="height: 400px;" onclick="this.select();"><?php echo $_assign_vars['configContent']; ?></textarea>
        <input type="hidden" name="isConfigWritable" value="<?= $_assign_vars['isConfigWritable'] ? '1' : '0'; ?>" />
    </div>
    <p style="text-align: center; color: #626262; padding-top: 19px; ">&bull; Create a cron job that runs <b>ow_cron/run.php</b> once a minute. (<a style="color:#2626ef;" target="_blank" href="http://docs.oxwall.org/install:cron"><b>?</b></a>)</p>
    <p align="center"><input type="submit" value="Continue" name="continue" style=" margin-bottom: 19px; text-transform: uppercase; font-size: 13px; font-family: 'Arial'; font-weight: bold; color: #777; /></p>
</form>