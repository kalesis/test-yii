<?php
/* @var $this SiteController */
/* @var $model ContactForm */
/* @var $form CActiveForm */

$this->pageTitle=Yii::app()->name . ' - Contactenos';

$this->widget('bootstrap.widgets.TbBreadcrumbs', array(
	'links'=>array('Contactenos'),
));
?>

<h1>Contáctenos</h1>

<?php if(Yii::app()->user->hasFlash('error')):?>
	<?php $this->widget('bootstrap.widgets.TbAlert', array(
		'block'=>true, // display a larger alert block?
		'fade'=>true, // use transitions?
		'closeText'=>'×', // close link text - if set to false, no close link is displayed
		'alerts'=>array( // configurations per alert type
				'success'=>array('block'=>true, 'fade'=>true, 'closeText'=>'×'), // success, info, warning, error or danger
		),
	)); ?>
<?php endif; ?>

<?php if( Yii::app()->user->hasFlash('success') ): ?>

	<?php $this->widget('bootstrap.widgets.TbAlert', array(
			'block'=>true, // display a larger alert block?
			'fade'=>true, // use transitions?
			'closeText'=>'×', // close link text - if set to false, no close link is displayed
			'alerts'=>array( // configurations per alert type
					'success'=>array('block'=>true, 'fade'=>true, 'closeText'=>'×'), // success, info, warning, error or danger
			),
	)); ?>

<?php else: ?>
	
	<p> Si tiene consultas comerciales u otras preguntas, por favor llene el siguiente formulario para contactar con nosotros. Gracias. </p>
	
	<?php /** @var BootActiveForm $form */
		$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
			'id'=>'horizontalForm',
			'type'=>'horizontal',
			'htmlOptions'=>array('class'=>'well'),
		));
	?>
		<?php echo $form->textFieldRow($model, 'name', array('class'=>'span3')); ?>
		<?php echo $form->textFieldRow($model, 'email', array('prepend'=>'@','class'=>'span4', 'hint'=>'[?] Se usara este email para responder su consulta.')); ?>
		<?php echo $form->textFieldRow($model, 'subject', array('append'=>'!','class'=>'span4')); ?>
		<?php echo $form->textAreaRow($model, 'body', array('class'=>'span4', 'rows'=>5)); ?>
		
		<?php if(CCaptcha::checkRequirements()): ?>
			<?php echo $form->captchaRow($model, 'verifyCode', array('class'=>'span2', 'hint'=>'[?] Por favor, introduzca las letras tal como se muestra en la imagen de arriba. Las letras no distinguen entre mayúsculas y minúsculas.')); ?>
		<?php endif; ?>
		
		<div class="btn-toolbar">
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'Login', 'type'=>'primary')); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'reset', 'label'=>'Reset')); ?>
		</div>
	
	<?php $this->endWidget(); ?>

<?php endif; ?>