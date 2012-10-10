<?php 
	/** ICrugeSystem
	
		@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
		@copyright Copyright &copy; 2008-2012 Yii Software LLC
		@license http://www.yiiframework.com/license/
	*/
interface ICrugeSystem {
	
	/*
		entrega el valor string de un atributo
	*/
	public function get($attribute);
	/*
		entrega el valor numerico de un atributo
	*/
	public function getn($attribute);
	/*
		encuentra un sistema por su nombre
	*/
	public static function findSystem($systemName);
	/*
		entrega un array de ICrugeSystem
	*/
	public static function listModels();
	/*
		retorna el nombre corto de un sistema
	*/
	public function getShortName();
	public function getLargeName();
	
	/*	
		@returns boolean true si el sistema esta disponible para iniciar sesion
	*/
	public function isAvailableForLogin();
	
	public function tableName();
	public function getPrimaryKey();
	
}