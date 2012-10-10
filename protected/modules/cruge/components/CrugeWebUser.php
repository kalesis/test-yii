	<?php /**
	CrugeWebUser

	es un gestor que permite manejar al usuario que ha iniciado sesion o que pretende iniciarla.
	consume a : CrugeUser

	esta clase necesita ser instalada en config, mediante:

	'components'=>array(
		'user'=>array(
			'allowAutoLogin'=>true,
			'class' => 'application.modules.cruge.components.CrugeWebUser',
		),

	una vez instalada puede ser accedida mediante:

	Yii::app()->user
	Yii::app()->user->isGuest()

	Todos los demás miembros de CWebUser se proveen, a excepcion de algunos que
	son sobreescritos.

	IMPORTANTE:

	No usar CHttpSession porque interfiere con el uso interno que se le da a $_SESSION dentro de CWebUser en los metodos getState setState.

	para almacenar valores usar $this->setState('nombreVariable','valor') y
	$this->getState('nombreVariable','defaultValue');


	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/
class CrugeWebUser extends CWebUser implements IWebUser {

	private $_lastError="";
	private $_access=array();	// cache para rbac por checkaccess
	private $_ui=null;
	private $_um =null;

	public function init()
	{
		parent::init();
	}
	/**
		nuevo metodo, para saber el error qe ocurrio, con traduccion incorporada
	*/
	public function getLastError(){
		return $this->_lastError;
	}
	/*	nuevo metodo que se accede via: Yii::app()->user->getUser();
		@returns instancia de ICrugeStoredUser o null
	*/
	public function getUser(){
		return $this->getum()->getUserFromSession($this->getICrugeSession());
	}
	/**
		da acceso directo al valor (solo lectura) de un campo personalizado.

		si el nombre del campo no existe o el usuario no ha inciado sesion se retorna ""

		ejemplo

			echo Yii::app()->user->getField('email');
			echo Yii::app()->user->getField('firstname');
	*/
	public function getField($fieldname){
		$user = $this->getUser();
		if($user != null){
			return $this->getum()->getFieldValue($user,$fieldname);
		}else
		return "";
	}
	/*
		helper para ayudar a controlar rapidamente el acceso a funciones que requieren
		de un usuario, si no hay sesion se emite una excepcion.
	*/
	public function noGuestAllowed(){
		if($this->isGuest)
			throw new CrugeException("debe iniciar sesion");
	}
	/*
		extension que permite consultar si este usuario (aun siendo invitado) tiene
		acceso o no a un determinado token de autenticacion identificado por su itemName.

		ejemplo:

		if(Yii::app()->user->checkAccess('createPostOperation')){
			..create post..
		}else{
			echo "access denied";
		}

		@itemName: nombre del item a ser comprobado para el usuario autenticado
		@params: los argumentos pasados al businessRule
		@descripcion: opcional, si rbacSetupEnabled es true, entonces se usara esta descripcion para crear el CAuthItem requerido cuando este no exista en la lista de operaciones.

		@returns true o false.  (si usuario activo es superadmin retorna true incondicionalmente)
	*/
	public function checkAccess($itemName,$descripcion="",$params=array()){

		// si esta habilitada la bandera de configuracion creara el CAuthItem si es requerido
		// y este no existe.
		//
		if(CrugeUtil::config()->rbacSetupEnabled == true){
			// esto no es eficiente en ambientes de produccion ya configurados plenamente
			// por tanto cuando se hayan establecido todos los permisos entonces
			// habra que deshabilitar este flag (rbacSetupEnabled) en la configuracion mayor
			if(!$this->getrbac()->getAuthItem($itemName))
				$this->getrbac()->createAuthItem($itemName,CAuthItem::TYPE_OPERATION
					,$descripcion);
		}

		if($this->isSuperAdmin){
			return true;
		}
		else
		{
			$ok = $this->getrbac()->checkAccess($itemName,$this->getId(),$params);
			if($ok == false){
				// no tiene el permiso asignado.
				// reportara el error para ser visualizado luego
				if(CrugeUtil::config()->rbacSetupEnabled == true){
					$ai = $this->getrbac()->getAuthItem($itemName);
					if($ai !== null){
						$this->getui()->addError($itemName
							,$this->getrbac()->getAuthItemTypeName($ai->type)
							,$descripcion
							);
					}
				}
			}
			return $ok;
		}
	}

	/*
		redirige al usuario a la pagina indicada por loginUrl en caso de que
		se detecte que es un invitado, luego del login es redirigido a la pagina a donde queria
		ir originalmente.

		este metodo es mejor usado en ambientes en donde no se esta usando a CAccessControlFilter, quien internamente invoca a Yii::app()->user->loginRequired cuando detecta que una regla ha fallado.
	*/
	public function checkLoginRequired(){
		if($this->isGuest)
			$this->loginRequired();
	}
	public function getIsSuperAdmin(){
		return ($this->name == CrugeUtil::config()->superuserName);
	}
	/*
		entrega un componente (CrugeUi) listo para ser usado, que se encarga de dar
		datos para la interfaz de usuario

		ejemplo:

		Yii::app()->user->ui
	*/
	public function getui(){
		if($this->_ui == null)
			$this->_ui = new CrugeUi();
		return $this->_ui;
	}
	public function getum(){
		if($this->_um == null)
			$this->_um = new CrugeUserManager();
		return $this->_um;
	}
	/*
		permite llamar al authManager directamente usando:
		Yii::app()->user->rbac

		previamente se debio declarar a CrugeAuthManager como la clase que administra a
		authmanager, eso se hace en components.
	*/
	public function getRbac(){
		return Yii::app()->getAuthManager();
	}
	/*
		permite conocer el sistema del usuario, si es un guest, el sistema sera nulo,
		si no es guest, dara la lista de sistemas del usuario a las que el pertenece
	*/
	/*
		TODO:


	*/

	/*  retorna el numero de usuario, tomado de la sesion iniciada
		si se quiere obtener acceso al usuario completo:
			Yii::app()->user->getUser()->getPrimaryKey()
	*/
	public function getId(){

		$_crugesesion = $this->getICrugeSession();
		if($_crugesesion == null)
			return CrugeUtil::config()->guestUserId;
		$userModel = $this->getum()->getUserFromSession($_crugesesion);
		if($userModel != null)
			return $userModel->getPrimaryKey();
		return CrugeUtil::config()->guestUserId;
	}
	public function getIsGuest(){
		return ($this->getId() == CrugeUtil::config()->guestUserId);
	}
	public function getName(){
		$model = $this->getICrugeSession();
		if($model != null){
			return $model->getSessionName();
		}
		else
		return CrugeTranslator::t("invitado");
	}
	public function getEmail(){
		$u = $this->getUser();
		if($u != null)
			return $u->email;
		return "";
	}


	/**
		se SUPONE que este metodo fue llamado tras un $identity->authenticate exitoso,
		por tanto estamos garantizando que identity->getId() tiene un identificador valido de
		un ICrugeStoredUser o un 0 si no se autentico.

		***el argumento $duration es pedido solo por compatibilidad, no se usa aqui.***

		la duracion del identificador en memoria de sesion dependera de la duracion de
		configuracion de PHP CONFIG, pero no asi la duracion del objeto de sesion (CrugeSession)
		el cual durara y sera reutilizado hasta que caduque o sea cerrado.
	*/
	public function login(/*IUserIdentity*/$identity,$duration = 0){

		if(get_class($identity) != 'CrugeUser')
			throw new CrugeException(
				"Por favor cambie las referencias a '".get_class($identity)."' por 'CrugeUser'"
			);


		Yii::log(__CLASS__."\nlogin\n","info");

		$this->_lastError = "";
		$this->clearSessionId();

		// carga el filtro de sesion habilitado para este modulo:
		$filtro = $this->getum()->getSessionFilter();

		// toma al usuario autenticado
		$user = $identity->getUser();
		if($user == null) {
			// no hay un usuario identificado para iniciar una sesion
			Yii::log(__CLASS__."\ngetUser is null\n","info");
			$this->_lastError = CrugeTranslator::t("debe autenticarse");
			return false;
		}

		$system = $this->getum()->getDefaultSystem();
		if($system == null)
		{
			Yii::log(__CLASS__."::login. systemName:".$_sname." no hallado.","error");
			throw new CrugeException("debe crear un registro en la tabla cruge_system");
		}

		// aplica credenciales sobre el sistema para obtener una sesion
		Yii::log(__CLASS__."\nfiltro->startSession\n","info");
		if(($usersession = $filtro->startSession($user,$system)) != null)
		{
			Yii::log(__CLASS__."\nfiltro->startSession OK\n","info");

			if($filtro->onStore($usersession)){
				// ahora si...guarda el identificador de sesion que getId devolvera
				$this->setSessionId($usersession->getPrimaryKey());
				$filtro->onLogin($usersession);
				return true;
			}
			else{
				Yii::log(CHtml::errorSummary($usersession,"error al guardar una sesion"),"error");
				$this->_lastError = CrugeTranslator::t("Error al almacenar sesion");
				return false;
			}
		}
		else
		{
			Yii::log(__CLASS__."\nfiltro->startSession error.\n".$filtro->getLastErrorDescr(),"info");

			$this->_lastError = $filtro->getLastErrorDescr();
			return false;
		}
	}
	public function logout($destroySession=true){
		$result=false;
		$usersession = $this->getICrugeSession();
		if($usersession != null){
			$filtro = $this->getum()->getSessionFilter();
			$usersession->logout();
			if($filtro->onStore($usersession)){
				$filtro->onLogout($usersession);
				$result=true;
			}
			else{
				Yii::log(CHtml::errorSummary($usersession,"error al guardar una sesion"),"error");
				$this->_lastError = CrugeTranslator::t("Error al almacenar sesion");
			}
		}
		parent::logout($destroySession);
		return $result;
	}
	/**HASTA AQUI llegan los metodos de la interfaz IWebUser*/

	/**

		estas funciones de aqui para abajo no pertenecen a la interfaz: IWebUser

	*/
	private function getSessionId(){
		return $this->getState('_sessionid_','0');
	}
	private function setSessionId($newValue){
		$this->setState('_sessionid_',$newValue);
	}
	private function clearSessionId(){
		$this->setState('_sessionid_','0');
	}
	/*
		carga una sesion de acuerdo al id persistente en cookies,
		pero al buscarlo lo revalida a ver si esta vigente, sino deriva en un evento
		de onSessionExpired
	*/
	private function getICrugeSession() {
		// idsession usado al momento del login almacenado en la memoria de sesion
		$model = $this->getum()->loadSession($this->getSessionId());
		if($model != null){
			if($model->validateSession()){
				return $model;
			}else{
				//if($model->isSessionExpired())
				$this->getum()->getSessionFilter()->onSessionExpired($model);
				// retorna null porque la sesion expiro.
				return null;
			}
		}
		else
		return null;
	}
};
