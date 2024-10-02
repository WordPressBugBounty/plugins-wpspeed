<?php

/** @var Document $this */
$tab = $this->container->input->get('tab', 'main');
?>

<div class="wrap">
	<div class="wrap wpspeed-header">
	    <h1><?php _e('WPSpeed', 'wpspeed');?><span>Version <?php echo WPSPEED_VERSION;?></span></h1>
	</div>
    <div class="tab-content">
	    <?php echo $this->getBuffer(); ?>
    </div>
</div>
