<?php
/** ICrugeFieldValue
	
	interfaz para inyectarle al ORDBM seleccionado los metodos a implementar relevante a campos
	personalizados y el valor asignado a un usuario.
	
	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/
interface ICrugeFieldValue {
	
	/*
		devuelve un objeto que implementa a ICrugeFieldValue
	*/
	public static function loadModel($id);
	public static function loadModelBy($iduser,$idfield);
	
	/**
		devuelve un array de objetos que implementan a ICrugeFieldValue
	*/
	public static function listModels($iduser);
	
	
	/**
		retorna el nombre de la tabla
	*/
	public function tableName();

	/*
		devuelve "el valor" del indice primario
	*/
	public function getPrimaryKey();
	
}
