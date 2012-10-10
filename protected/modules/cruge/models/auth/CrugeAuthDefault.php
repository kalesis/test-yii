<?php
/**
	CrugeAuthDefault

	Implementa un modo de autenticacion basado en la lista de usuarios registrados reales
	almacenados con CrugeStoredUser.

	aqui se hara uso de CrugeModule::availableAuthModes
	y de CrugeModule::useEncryptedPassword

	esta clase es consumida por: CrugeUser::authenticate()
	quien a su vez es invocada por CrugeLogon

 	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/
class CrugeAuthDefault extends CBaseUserIdentity implements ICrugeAuth {

	public $username;
	public $password;
	public $options;

	private $_userinstance = null;

	private function _getPwd(){
		if(CrugeUtil::config()->useEncryptedPassword == true)
			return md5($this->password);
		return $this->password;
	}

	/**
		este nombre sera referenciado en config/main para hacerle saber a Cruge que use esta clase
		para autenticar:

			'availableAuthMethods'=>array('authdemo'),
	*/
	public function authName(){
		return "default";
	}

	/*	no confundir con un getUserName, esto es un getUser a nivel de instancia

		@returns instancia de ICrugeStoredUser hallado tras la autenticacion exitosa
	*/
	public function getUser(){
		return $this->_userinstance;
	}

	public function setParameters($username,$password,$options = array()){
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;
	}

	public function authenticate() {

		Yii::log(__METHOD__,"info");

		$this->errorCode = self::ERROR_USERNAME_INVALID;

		$model = Yii::app()->user->um->loadUser($this->username);

		Yii::log(__METHOD__." usuario retornado es:\n".CJSON::encode($model),"info");

		$this->_userinstance = null;
		if($model != null)
		{
			if($model->password == $this->_getPwd()){
				$this->_userinstance = $model;
				$this->errorCode=self::ERROR_NONE;
			}
			else{
				if(CrugeUtil::config()->debug == true){
					// ayuda a instalar, quiza el usuario olvide quitar la encriptacion de claves
					// y reciba error de ERROR_PASSWORD_INVALID, es porque esta actuando el MD5
					// y el usuario recien creado trae una clave no encritpada
					if(CrugeUtil::config()->useEncryptedPassword == true){
						echo Yii::app()->user->ui->setupAlert(
							"Quiza su clave no coincide porque ha configurado 'useEncryptedPassword = true' estando en la fase de instalacion, pruebe deshabilitandolo");
					}
				}
				$this->errorCode=self::ERROR_PASSWORD_INVALID;
			}
		}
		else{
			// username o email error
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		}

		Yii::log(__CLASS__."\nauthenticate returns:\n".$this->errorCode
			."\n boolean result is:".($this->errorCode==self::ERROR_NONE),"info");


		return $this->errorCode==self::ERROR_NONE;
	}
}

