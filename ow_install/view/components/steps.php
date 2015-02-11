<div class="steps">
    <?php $iteration = 1; ?>
    <?php foreach ($_assign_vars['steps'] as $step) { ?>
            <?php
                if ( $iteration == 1 ) $class = 'borderright';
                else if ( $iteration == count($_assign_vars['steps']) ) $class = 'borderleft';
                else $class = 'borderleft borderright';
                $iteration ++;
            ?>
	        <span <?php if ($step['active']) echo 'class="activepointer"'; ?> >
	            <span class="<?php echo $class; ?>">
	                <span class="item <?php if ($step['active']) echo 'active"'; ?>">
	                    <?php echo $step['label']; ?>
	                </span>
	            </span>
	        </span>
    <?php } ?>
</div>