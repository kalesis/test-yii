<?php 
$this->pageTitle=Yii::app()->name . ' - Logeo';

$this->widget('bootstrap.widgets.TbBreadcrumbs', array(
		'links'=>array('Logeo'),
));
?>

<h1><?php echo CrugeTranslator::t("Iniciar Sesion"); ?></h1>

<?php if(Yii::app()->user->hasFlash('loginflash')): ?>
<div class="flash-error">
	<?php echo Yii::app()->user->getFlash('loginflash'); ?>
</div>

<?php else: ?>

<?php 
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'verticalForm',
	'enableClientValidation'=>false,
	'htmlOptions'=>array('class'=>'well'),
)); 


	echo $form->textFieldRow($model, 'username', array('class'=>'span3')); 
	echo $form->passwordFieldRow($model, 'password', array('class'=>'span3')); 
	
	echo $form->checkboxRow($model, 'rememberMe'); 
?>

<div class="btn-toolbar">

	<?php 
	$this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Login', 'type'=>'primary')); 
	
	$this->widget('bootstrap.widgets.TbButtonGroup', array(
		'size' => 'small', 
		'type'=>'info',
		'buttons'=>array(
			array('label'=>'Recuperar Clave', 'url'=>array('ui/pwdrec') ),
			array('label'=>'Registrarse', 'url'=>array('ui/registration')),
			
		),
	));
	?>

</div>
	
<?php $this->endWidget(); ?>

<?php endif; ?>