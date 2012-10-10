<?php 
$this->widget('zii.widgets.CListView', array(
	'id'=>'list-auth-items',
    'dataProvider'=>$dataProvider,
    'itemView'=>'_authitem',
    'sortableAttributes'=>array(
        'name',
    ),
));	
?>
<script>
	$('#list-auth-items .referencias').each(function(){
		$(this).click(function(){
			$(this).parent().find('ul').toggle('slow');
		});
	});
</script>