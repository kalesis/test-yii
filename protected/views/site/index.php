<?php
/* @var $this SiteController */
$this->pageTitle=Yii::app()->name;
?>
<br/>
<center>
<?php
$this->widget('bootstrap.widgets.TbCarousel', array(
	'items'=>array(
		array(
		  'image'=>'http://placehold.it/830x400&text=First+thumbnail',
		  'label'=>'First Thumbnail label',
		  'caption'=>'Cras justo odio, dapibus ac facilisis in, egestas eget quam. ' .
			  'Donec id elit non mi porta gravida at eget metus. ' .
			  'Nullam id dolor id nibh ultricies vehicula ut id elit.'),
		array(
		  'image'=>'http://placehold.it/830x400&text=Second+thumbnail',
		  'label'=>'Second Thumbnail label',
		  'caption'=>'Cras justo odio, dapibus ac facilisis in, egestas eget quam. ' .
			  'Donec id elit non mi porta gravida at eget metus. ' .
			  'Nullam id dolor id nibh ultricies vehicula ut id elit.'),
		array(
		  'image'=>'http://placehold.it/830x400&text=Third+thumbnail',
		  'label'=>'Third Thumbnail label',
		  'caption'=>'Cras justo odio, dapibus ac facilisis in, egestas eget quam. ' .
			  'Donec id elit non mi porta gravida at eget metus. ' .
			  'Nullam id dolor id nibh ultricies vehicula ut id elit.'),
	),
));

?>
</center>