<?php 
	// llamada cuando el actionRegistration ha insertado a un usuario
?>
<div class='form'>
	<h1><?php echo CrugeTranslator::t("Bienvenido");?></h1>
	
	<p><b><?php echo CrugeTranslator::t("La cuenta ha sido creada !"); ?></b></p>
	<p><?php echo CrugeTranslator::t("haga click aqui para iniciar sesion con sus nuevas credenciales:"); ?>
			<?php echo Yii::app()->user->ui->loginLink; ?>
		</p>
</div>