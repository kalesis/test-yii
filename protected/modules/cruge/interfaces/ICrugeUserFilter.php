<?php 
/** ICrugeUserFilter

	interfaz usada por Yii::app()->user->save(..) para aceptar a un usuario que pretende
	registrarse.
	
	si esta interfaz retorna false, tambien debe informar el error al modelo mediante
	la llamada a addError('fieldname','error descripcion');
	
	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/
	interface ICrugeUserFilter {
		public function canInsert(ICrugeStoredUser $model);
		public function canUpdate(ICrugeStoredUser $model);
	}
?>