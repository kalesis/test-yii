<?php 
	/*
		esta es una subvista referenciada por: 
				_listauthitems.php
		quien a su vez es renderizada por:
				rbaclisttasks.php
				rbaclistroles.php
				rbaclistops.php
			
		$data es una instancia de CAuthItem
	*/
	
	$asignaciones = Yii::app()->user->rbac->getCountUsersAssigned($data->name);
	
	$referencias =  Yii::app()->user->rbac->getParents($data->name);
	$count_ref = count($referencias);
	
?>

<div class='row'>
	<div class='col authname'><?php echo $data->name;?></div>
	
	
	<div class='col operacion'>
		<?php echo CHtml::link(CrugeTranslator::t("propiedades"),
			Yii::app()->user->ui->getRbacAuthItemUpdateUrl($data->name));?>
	</div>

	<?php if($data->type != CAuthItem::TYPE_OPERATION) { ?>
	<div class='col operacion'>
		<?php echo CHtml::link(CrugeTranslator::t("editar permisos"),
			Yii::app()->user->ui->getRbacAuthItemChildItemsUrl($data->name));?>
	</div>
	<?php } ?>

	<div class='col operacion'>
		<b><?php 
			if($asignaciones > 0) 
				echo "<span style='cursor: pointer;' title='".CrugeTranslator::t("Usuarios a los que les ha sido asignado este ".Yii::app()->user->rbac->getAuthItemTypeName($data->type))."'>".$asignaciones."&nbsp;".CrugeTranslator::t("asignaciones")."</span>";
			?>
		</b>
	</div>
	
	<div class='col operacion'>
		<?php 	
			$tit = CrugeTranslator::t(
				"muestra aquellos objetos que hacen referencia a ")." ".$data->name."";
			if($count_ref > 0) {
				echo "<a class='referencias' title='$tit' href='#'>".$count_ref." refs.</a>";
				echo "<ul class='detallar-referencias'>";
				foreach($referencias as $ref)
					echo "<li>".CHtml::link(
						$ref->name
						,Yii::app()->user->ui->getRbacAuthItemChildItemsUrl($ref->name)
						,array('target'=>'_blank')
						)."</li>";
				echo "</ul>";
			}
			?>
	</div>
	
	<div class='col operacion operacion-eliminar'>
		<?php 
			$url = '#';
			$imagen = 'delete-off.png';
			$titulo='no puede eliminar porque tiene asignaciones';
			if($asignaciones == 0)
			{
				$titulo='eliminar';
				$url = Yii::app()->user->ui->getRbacAuthItemDeleteUrl($data->name);
				$imagen = 'delete.png';
			}
			echo CHtml::link(CHtml::image(
				Yii::app()->user->ui->getResource($imagen)),$url
				,array('title'=>CrugeTranslator::t($titulo))
				);
		?>
	</div>
	
	
	<div class='col descr'>	
		<?php 	
			if(trim($data->description) != '')
				echo "<hr/>".$data->description;
		?>
	</div>
</div>