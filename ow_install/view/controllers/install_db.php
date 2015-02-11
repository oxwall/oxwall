<p style="font-size: 16px; font-family: Arial; margin: -42px 0px 18px; color: rgb(98, 98, 98);border-bottom: 1px solid #e9eaeb; padding-bottom: 20px;">Database </p>

<?php echo install_tpl_feedback(); ?>
<form method="post">
    <table style=" font-size: 15px;" class="form"> <p style=" color: #626262; text-align: center;"> Please create a database and enter its details here. </p>

        <tr> 
            <td class="label">Host</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_host'); ?>">
               <input type="text" name="db_host" value="<?php echo @$_assign_vars['data']['db_host']; ?>" />
            </td>
            <td class="description">MySQL host and port (optionally). Example: <i>localhost</i> or <i>localhost:3307</i></td>
        </tr>
        <tr>
            <td class="label">User</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_user'); ?>">
               <input type="text" name="db_user" value="<?php echo @$_assign_vars['data']['db_user']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        <tr>
            <td class="label">Password</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_password'); ?>">
               <input type="text" name="db_password" value="<?php echo @$_assign_vars['data']['db_password']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        
        <tr>
            <td class="label">Database Name</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_name'); ?>">
               <input type="text" name="db_name" value="<?php echo @$_assign_vars['data']['db_name']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        
        <tr>
            <td class="label">Table prefix</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_prefix'); ?>">
               <input type="text" name="db_prefix" value="<?php echo @$_assign_vars['data']['db_prefix']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
    </table>

    <p align="center" style="margin: 10px 0 20px 0;" ><input type="submit" value="Continue" style=" text-transform: uppercase; font-size: 13px; font-family: 'Arial'; font-weight: bold; color: #777;/></p>

</form>