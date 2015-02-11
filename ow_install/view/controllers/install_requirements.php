<p style=" font-size: 16px; font-family:Arial; color: #626262; margin: -42px 0 18px 0; border-bottom: 1px solid #e9eaeb; padding-bottom: 20px;" > Hosting Server Requirements</p>

<p class="red">
	Your hosting account doesn't meet the following requirements:
</p>

<ul class="ow_regular">
<!-- PHP version -->
<?php if ( !empty($_assign_vars['fails']['php']['version']) ) { $requiredVersion = $_assign_vars['fails']['php']['version']; ?>
    
        <li>
               Required PHP version: <b class="high"><?php echo $requiredVersion ?></b> or higher <span class="small">(currently <b><?php echo $_assign_vars['current']['php']['version']; ?></b>)</span>
        </li>
    
<?php } ?>

<!-- PHP extensions -->
<?php if ( !empty($_assign_vars['fails']['php']['extensions']) ) { ?>
    <?php foreach ($_assign_vars['fails']['php']['extensions'] as $requiredExt) { ?>
        
        <li>
               <b class="high"><?php echo $requiredExt; ?></b> PHP extension not installed
        </li>    
            
    <?php } ?>
<?php } ?>

<!-- INI Configs -->
<?php if ( !empty($_assign_vars['fails']['ini']) ) { ?>
    
        <?php foreach ($_assign_vars['fails']['ini'] as $iniName => $iniValue) { ?>
        
            <li>
                   <span class="high"><?php echo $iniName; ?></span> must be <b class="high"><?php echo $iniValue ? 'on' : 'off'; ?></b>
                   <span class="small">(currently <b><?php echo $_assign_vars['current']['ini'][$iniName] ? 'on' : 'off'; ?></b>)</span>
            </li>    
                
        <?php } ?>
    
<?php } ?>

<!-- GD version -->
<?php if ( !empty($_assign_vars['fails']['gd']['version']) ) { $requiredVersion = $_assign_vars['fails']['gd']['version']; ?>
    
        <li>
               Required <span class="high">GD library</span> version: <b class="high"><?php echo $requiredVersion ?></b> or higher 
               <span class="small">(currently <b><?php echo $_assign_vars['current']['gd']['version']; ?></b>)</span>
        </li>
    
<?php } ?>

<!-- GD support -->
<?php if ( !empty($_assign_vars['fails']['gd']['support']) ) { $requiredSupportType = $_assign_vars['fails']['gd']['support']; ?>
    
        <li>
               <b class="high"><?php echo $requiredSupportType ?></b> required for <span class="high">GD library</span>
        </li>
    
<?php } ?>

</ul>

<p>
	Please correct these before you can proceed with Oxwall installation. Complete server requirements list and compatible hosting can be found at <a href="http://www.oxwall.org/hosting">Oxwall.org/hosting</a>
</p>