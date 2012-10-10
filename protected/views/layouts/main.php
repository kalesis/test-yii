<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	
	<title>.: <?php echo CHtml::encode($this->pageTitle); ?> :.</title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css">
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/normalize.css">
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/styles.css"/>
	<!--using less instead? file not included-->
	<!--<link rel="stylesheet/less" type="text/css" href="/less/styles.less">-->

	<!--<script src="/less/less-1.3.0.min.js"></script>-->
	<link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/favicon.ico">
	
</head>

<body>

<?php
$this->widget('bootstrap.widgets.TbNavbar', array(
	'brand' => Yii::app()->name,
	'type' => 'inverse',
	'items' => array(
		array(
			'class' => 'bootstrap.widgets.TbMenu',
			'items' => array(
				array('label'=>'Inicio', 'url'=>array('/site/index')),
				array('label'=>'Contactenos', 'url'=>array('/site/contact')),
				array('label'=>'Administrar Usuarios' , 'url'=>Yii::app()->user->ui->userManagementAdminUrl, 'visible'=>!Yii::app()->user->isGuest),
				array('label'=>'Agil', 'items'=> array(
					array('label'=>'Proyecto', 'url'=>array('/proyecto/admin'), 'visible'=>!Yii::app()->user->isGuest),
				)),
				array('label'=>'Intranet', 'items'=> array(
					array('label'=>'Acerca de', 'url'=>array('/site/page', 'view'=>'about')), 
					'---',
					array('label'=>'Logeo', 'url'=>Yii::app()->user->ui->loginUrl, 'visible'=>Yii::app()->user->isGuest), //'url'=>Yii::app()->user->ui->loginUrl
					array('label'=>'Salir ('.Yii::app()->user->name.')', 'url'=>Yii::app()->user->ui->logoutUrl, 'visible'=>!Yii::app()->user->isGuest),
				)),
				
			)
		),
		'<form class="navbar-search pull-right" action=""><input type="text" class="search-query span2" placeholder="Buscar"></form>',
	)
));
?>
	
<div class="container">	
	<?php echo $content?>
</div>

<div class="footer">
	<div class="container">
	&copy; <?php echo date('Y'); ?> by IC Projects<br/>
	</div>
</div><!-- footer -->
</body>
</html>