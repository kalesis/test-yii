<?php 
	// permite al usuario seleccionar los AuthItems que conforman a este ROL o TASK,
	// las OPERATIONS no tienen childs.
	//
	// argumentos recibidos:
	//
	// $model: instancia de CAuthItem
	//
	Yii::app()->clientScript->registerCoreScript('jquery');
	
	$rbac = Yii::app()->user->rbac;
	
	echo "<h1>".ucfirst($model->name)." (".
		CrugeTranslator::t($rbac->getAuthItemTypeName($model->type)).")</h1>";
		
	echo "<h3 class='hint'>".$model->description."</h3>";
	
	$roles = $rbac->getRoles();
	$tareas = $rbac->getTasks();
	$operaciones = $rbac->getOperations();

	// estos son los items asignados a la instancia de CAuthItem seleccionado ($model)
	//
	$childrens = $rbac->getItemChildren($model->name);

	echo "<p>".ucfirst(CrugeTranslator::t(
	"haga click en un item para activarlo o desactivarlo"))."</p>";

	if((count($roles) > 0) && ($model->type != CAuthItem::TYPE_TASK)){
		echo "<hr/><h3>".ucfirst($rbac->getAuthItemTypeName(CAuthItem::TYPE_ROLE,true))."</h3>";
		echo "<ul class='auth-item'>";
		foreach($roles as $item){
			
			$yaVieneMarcado = isset($childrens[$item->name]);
			$checked = '';
			if($yaVieneMarcado)
				$checked = 'checked';
			
			$loader = "<span class='loader'></span>";
			$loop = $rbac->detectLoop($model->name,$item->name) ? "loop" : "" ;
			
			echo "<li class='{$checked} {$loop}' alt='".$item->name."'>".$item->name.$loader."</li>";
		}	
		echo "</ul>";
	}

	
	if(count($tareas) > 0){
		echo "<hr/><h3>".ucfirst($rbac->getAuthItemTypeName(CAuthItem::TYPE_TASK,true))."</h3>";
		echo "<ul class='auth-item'>";
		foreach($tareas as $item){
			
			$yaVieneMarcado = isset($childrens[$item->name]);
			$checked = '';
			if($yaVieneMarcado)
				$checked = 'checked';
			
			$loader = "<span class='loader'></span>";
			$loop = $rbac->detectLoop($model->name,$item->name) ? "loop" : "" ;
			
			echo "<li class='{$checked} {$loop}' alt='".$item->name."'>".$item->name.$loader."</li>";
		}	
		echo "</ul>";
	}

	if(count($operaciones) > 0){
		echo "<hr/><h3>".ucfirst($rbac->getAuthItemTypeName(CAuthItem::TYPE_OPERATION,true))."</h3>";
		echo "<ul class='auth-item'>";
		foreach($operaciones as $item){
			
			$yaVieneMarcado = isset($childrens[$item->name]);
			$checked = '';
			if($yaVieneMarcado)
				$checked = 'checked';
			
			$loader = "<span class='loader'></span>";
			$loop = $rbac->detectLoop($model->name,$item->name) ? "loop" : "" ;
			
			echo "<li class='{$checked} {$loop}' alt='".$item->name."'>".$item->name.$loader."</li>";
		}	
		echo "</ul>";
	}

	
?>


<script>
	$('li').each(function(){
		var li = $(this);
		li.css("cursor","pointer");
		li.click(function(){

			// el atributo alt del LI tiene el nombre del item que representa.
			var _li = $(this);
			var thisItemName = _li.attr('alt');
			var setFlag = _li.hasClass('checked') ? false : true;
			var action = '<?php echo Yii::app()->user->ui->getRbacAjaxSetChildItemUrl()?>';
			var jsondata = "{ \"parent\": \"<?php echo $model->name;?>\" , \"child\": "
					+"\""+thisItemName+"\" , \"setflag\": "+setFlag+" }";	
			var loadingUrl = '<?php echo Yii::app()->user->ui->getResource('loading.gif'); ?>';
			var loader = li.find('span.loader');

			loader.html("<img src='"+loadingUrl+"'>");
			$('#_errorResult').html("");
			jQuery.ajax({
				url: action,
				type: 'post',
				async: true,
				contentType: "application/json",
				data: jsondata,
				success: function(data, textStatus, jqXHR){
					loader.html("");
					// si se pudo realizar la accion, aqui data trae un objeto json con la data del // item
					if(data.result == true){
						_li.addClass("checked");
					}else{
						_li.removeClass("checked");
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					//$('#_errorResult').html("Ocurrio un error:<hr>"+jqXHR.responseText);
					$('#_errorResult').html("<p class='auth-item-error-msg'>no se pudo agregar</p>");
					$('#_errorResult').show("slow");
					setTimeout(function(){
						$('#_errorResult').hide("slow");
						$('#_errorResult').html("");
					},3000);
					loader.html("");
				},
			});
		});
	});
	
	
</script>

<div id='_errorResult'></div>
<div id='_log'></div>

<?php 
	$ayuda = "";
	if($model->type == CAuthItem::TYPE_ROLE)
		$ayuda = CrugeTranslator::t(
		"Los roles pueden estar comprendidos de otros roles, tareas u operaciones. el sistema evitara que se hagan ciclos (loops).
		");
	if($model->type == CAuthItem::TYPE_TASK)
		$ayuda = CrugeTranslator::t(
		"Las tareas pueden estar comprendidos de otras tareas u operaciones. los roles incluyen a tareas por eso no estan presentes aqui para ser seleccionados. el sistema evitara que se hagan ciclos (loops).
		");
	echo "<p class='hint'>$ayuda</p>";
?>

