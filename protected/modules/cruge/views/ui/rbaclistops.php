<h1><?php echo ucwords(CrugeTranslator::t("operaciones"));?></h1>

<div class='auth-item-create-button'>
<?php echo CHtml::link(CrugeTranslator::t("Crear Nueva Operacion")
	,Yii::app()->user->ui->getRbacAuthItemCreateUrl(CAuthItem::TYPE_OPERATION));?>
</div>

<?php $this->renderPartial('_listauthitems'
	,array('dataProvider'=>$dataProvider)
	,false
	);?>