<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>
<div class="span-19">
	<div id="content">
		<?php echo $content; ?>
	</div><!-- content -->
</div>
<div class="span-5 last">
	<?php 
	$this->widget('bootstrap.widgets.TbMenu', array(
		'type'=>'list',
		'items'=>$this->menu,
	));
	?>

	
</div>
<?php $this->endContent(); ?>