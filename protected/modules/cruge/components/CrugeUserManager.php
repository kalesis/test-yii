<?php
/** CrugeUserManager

	funciona como una interfaz para el core del sistema cruge, opera como un API.

	se accede exclusivamente asi:

		$um = Yii::app()->user->um;

	dependencias:

		CrugeFactory
		CrugeUtil
		CrugeTranslator

 	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/

define("CRUGEUSERSTATE_NOTACTIVATED",0);
define("CRUGEUSERSTATE_ACTIVATED",1);
define("CRUGEUSERSTATE_SUSPENDED",2);

define("CRUGEFIELDTYPE_TEXTBOX",0);
define("CRUGEFIELDTYPE_TEXTAREA",1);
define("CRUGEFIELDTYPE_BOOLEAN",2);
define("CRUGEFIELDTYPE_LISTBOX",3);

define("CRUGE_ACTIVATION_OPTION_INMEDIATE",0);
define("CRUGE_ACTIVATION_OPTION_EMAIL",1);
define("CRUGE_ACTIVATION_OPTION_MANUAL",2);



class CrugeUserManager  {

	/*
		retorna un array con los estatus que puede tener un usuario.
		este array tambien puede ser utilizado directamente en un dropDownList
	*/
	public function getUserStateOptions(){
		$stAr=array();
		for($i=CRUGEUSERSTATE_NOTACTIVATED;$i<=CRUGEUSERSTATE_SUSPENDED;$i++)
		   $stAr[$i] = $this->getStateName($i);
		return $stAr;
	}
	public function getStateName($state){
		switch($state)
		{
			case CRUGEUSERSTATE_NOTACTIVATED:
				return CrugeTranslator::t("Cuenta sin Activar");
			case CRUGEUSERSTATE_ACTIVATED:
				return CrugeTranslator::t("Cuenta Activada");
			case CRUGEUSERSTATE_SUSPENDED:
				return CrugeTranslator::t("Cuenta Suspendida");
		}
		return $state;
	}
	public function getFieldTypeOptions(){
		$stAr=array();
		for($i=CRUGEFIELDTYPE_TEXTBOX;$i<=CRUGEFIELDTYPE_LISTBOX;$i++)
		   $stAr[$i] = $this->getFieldTypeName($i);
		return $stAr;
	}
	public function getFieldTypeName($fieldType){
		switch($fieldType)
		{
			case CRUGEFIELDTYPE_TEXTBOX:
				return CrugeTranslator::t("TextBox");
			case CRUGEFIELDTYPE_TEXTAREA:
				return CrugeTranslator::t("TextArea");
			case CRUGEFIELDTYPE_BOOLEAN:
				return CrugeTranslator::t("CheckBox");
			case CRUGEFIELDTYPE_LISTBOX:
				return CrugeTranslator::t("ListBox");
		}
		return $fieldType;
	}

	public function getUserActivationOptions(){
		$stAr=array();
		for($i=CRUGE_ACTIVATION_OPTION_INMEDIATE;$i<=CRUGE_ACTIVATION_OPTION_MANUAL;$i++)
		   $stAr[$i] = $this->getUserActivationName($i);
		return $stAr;
	}
	public function getUserActivationName($state){
		switch($state)
		{
			case CRUGE_ACTIVATION_OPTION_INMEDIATE:
				return CrugeTranslator::t("Activacion inmediata");
			case CRUGE_ACTIVATION_OPTION_EMAIL:
				return CrugeTranslator::t("Activar mediante correo");
			case CRUGE_ACTIVATION_OPTION_MANUAL:
				return CrugeTranslator::t("Activacion manual");
		}
		return $state;
	}



	/* se encarga de crear una nueva llave de autenticacion para el usuario.
	   el modelo debera ser guardado tras esta llamada.

		@see getActivationUrl
	*/
	public function generateAuthenticationKey(ICrugeStoredUser $user){
		$user->authkey = md5($user->username."-".$user->password);
	}


	/*
		entrega una CArrayDataProvider obtenido desde un array de userid.

		es una funcion helper que invoca a listUsers con los parametros adecuados.
	*/
	public function listUsersDataProviderFromArray($arrayUserId,$pageSize=20){
		return $this->listUsers(null,true,$pageSize,true,$arrayUserId);
	}
	public function listAllUsersDataProvider($params=array(),$pageSize=20){
		return $this->listUsers($params,true,$pageSize,false,null);
	}
	/*
		@param, lista de parametros a pasar a ICrugeStoredUser::listmodels
		@booleanAsDataProvider, false=retorna un array, true=retorna un carraydataprovider
		@pageSize, solo tiene sentido bajo un carraydataprovider
		@buildFromThisUsersIdArray, si se especifica el dataprovider se construira en base
		a estos iduser entregados en un array

		retorna un array de objetos ICrugeStoredUser o un CArrayDataProvider
	*/
	public function listUsers(
		 $param=array()
		,$booleanAsDataProvider=false
		,$pageSize=20
		,$boolUseArray=false
		,$buildFromThisUsersIdArray=null
	){
		$ar=array();
		// si buildFromThisUsersIdArray es null, entonces se buscan los usuarios directamente
		if($boolUseArray == false){
			$ar = CrugeFactory::get()->getICrugeStoredUserList($param);
		}else{
			if($buildFromThisUsersIdArray != null)
			foreach($buildFromThisUsersIdArray as $userid){
				$user = $this->loadUserById($userid);
				$ar[] = $user;
			}
		}



		if($booleanAsDataProvider == true){
			$sortFields = CrugeFactory::get()->getICrugeStoredUserSortFieldNames();
			return new CArrayDataProvider($ar, array(
				'keyField'=>$sortFields[0],
				'sort'=>array(
					'attributes'=>$sortFields,
				),
				'pagination'=>array(
					'pageSize'=>$pageSize,
				),
			));
		}
		else
			return $ar;
	}

	/*
		@returns instancia ICrugeStoredUser del usuario cuyo iduser sea el $id pasado por argumento.

		para que el user cargado tenga los campos de perfil hay que llamar a:
		@see loadUserFields (poner el arg a true: $boolAndLoadFields)
		@see loadUser
	*/
	public function loadUserById($id,$boolAndLoadFields=false){
		$user = CrugeFactory::get()->getICrugeStoredUserLoadModel($id,false);
		if(($boolAndLoadFields == true) && ($user != null))
			$this->loadUserFields($user);
		return $user;
	}
	public function loadUserByKey($id,$boolAndLoadFields=false){
		$user = CrugeFactory::get()->getICrugeStoredUserLoadModel($id,false,true);
		if(($boolAndLoadFields == true) && ($user != null))
			$this->loadUserFields($user);
		return $user;
	}
	/*
		@returns instancia ICrugeStoredUser del usuario cuyo iduser sea el $id pasado por argumento.

		para que el user cargado tenga los campos de perfil hay que llamar a:
		@see loadUserFields (poner el arg a true: $boolAndLoadFields)
		@see loadUserById
	*/
	public function loadUser($usernameOrEmail,$boolAndLoadFields=false){
		Yii::log(__METHOD__."\nusernameOrEmail=".$usernameOrEmail,"info");
		$user = CrugeFactory::get()->getICrugeStoredUser($usernameOrEmail);
		if(($boolAndLoadFields == true) && ($user != null))
			$this->loadUserFields($user);
		return $user;
	}

	/*
		crea una nueva instancia de ICrugeStoredUser
	*/
	public function createBlankUser(){
		$user = CrugeFactory::get()->getICrugeStoredUserNewModel();
		if($user != null){
			// asegura que no falle al validar por terminos y condiciones
			$user->terminosYCondiciones = true;
			// asegura que no falle al validar por captcha
			//	cruge\models\data\CrugeStoredUser.php (bypassCaptcha y _getCaptchaRule)
			$user->bypassCaptcha = true;
			return $user;
		}else
		return null;
	}

	/*
		activa la cuenta, estampando la fecha de activacion.

		solo aplica si el estado del modelo es: CRUGEUSERSTATE_NOTACTIVATED
	*/
	public function activateAccount(ICrugeStoredUser $user){
		if($user->state != CRUGEUSERSTATE_NOTACTIVATED)
			return false;
		$user->state = CRUGEUSERSTATE_ACTIVATED;
		$user->actdate = CrugeUtil::now();
	}
	/*
		entrega la URL de activacion
		@see generateAuthenticationKey
	*/
	public function getActivationUrl(ICrugeStoredUser $user){
		return
			 rtrim(CrugeUtil::config()->baseUrl,"/")
			.CrugeUtil::uiaction('activationurl',array('key'=>$user->authkey));
	}

	/*
		marca la fecha de logon del usuario, normalmente para alterar el campo logondate,
	*/
	public function recordLogon(ICrugeStoredUser $user){
		$user->logondate = CrugeUtil::now();
	}

	/*
		guarda a este usuario, bajo un escenario especial llamado 'internal', para
		poder pasar por encima de algunas reglas de validacion que puede que apliquen
		solo para el usuario que manipula el modelo mediante formularios.

		si el escenario es 'insert' (caso crear usuario o registrar usuario),
		entonces se aplica un filtro de registro instanciado por algun ICrugeRegistrationFilter
		declarado en la configuracion del modulo Cruge.

	*/
	public function save(ICrugeStoredUser $user,$scenario='internal'){
		$user->scenario = $scenario;
		// aplica el filtro ICrugeUserFilter configurado en el modulo
		//
		if(($user->scenario == 'insert') || ($user->scenario == 'update')) {
			$filtro = CrugeFactory::get()->getUserFilter();
			if($filtro != null){
				if($user->scenario == 'insert'){
					if($filtro->canInsert($user) == false)
						return false;
				}
				else
				if($user->scenario == 'update')
					if($filtro->canUpdate($user) == false)
						return false;
			}
		}
		return $user->save();
	}


	/*
		le cambia la clave al usuario.  el modelo debera ser guardado con $model->save() tras
		esta llamada.
	*/
	public function changePassword(ICrugeStoredUser $user, $newPassword){
		$epwd = $newPassword;
		if(CrugeUtil::config()->useEncryptedPassword == true)
			$epwd = md5($newPassword);
		$user->password = $epwd;
	}

	/* busca la sesion abierta mas reciente del usuario

		returns ICrugeSession la sesion abierta mas reciente del usuario
	*/
	public function findSession(ICrugeStoredUser $user){
		return CrugeFactory::get()->getICrugeSessionFindLastByUser($user->getPrimaryKey());
	}
	public function loadSession($idsession){
		return CrugeFactory::get()->getICrugeSession($idsession);
	}
	/*
		retorna instancia de ICrugeSessionFilter del filtro de sesion instalado
	*/
	public function getSessionFilter(){
		return CrugeFactory::get()->getICrugeSessionFilter();
	}


	/*
		busca un ICrugeSystem por su nombre
	*/
	public function loadSystemByName($systemName){
		return CrugeFactory::get()->getICrugeSystemByName($systemName);
	}
	public function getDefaultSystem(){
		return $this->loadSystemByName('default');
	}


	/* crea una nueva sesion para el usuario basado en los parametros del sistema seleccionado.

	   returns ICrugeSession
	*/
	public function createSession(ICrugeStoredUser $user,ICrugeSystem $sys){

		Yii::log(__CLASS__."::createSession. user=#"
			.$user->getPrimaryKey(),"info");

		return CrugeFactory::get()->getICrugeSessionCreate(
			$user->getPrimaryKey(),$sys->getn('sessionmaxdurationmins'));
	}

	/*
		retorna una instancia de ICrugeStoredUser de la sesion indicada.
	*/
	public function getUserFromSession(ICrugeSession $session) {
		return CrugeFactory::get()->getSessionUser($session);
	}

	/*
		carga un filtro que implementa a ICrugeAuth hallado por su nombre
	*/
	public function getAuthenticationFilterByName($byName)
	{
		return CrugeFactory::get()->getICrugeAuthByName($byName);
	}

	/*
		retorna una instancia de ICrugeField buscada por su idfield.
	*/
	public function loadFieldById($id){
		return CrugeFactory::get()->getICrugeFieldLoadModel($id);
	}
	/*
		retorna una instancia de ICrugeField buscada por su fieldname
	*/
	public function loadFieldByName($fieldname){
		return CrugeFactory::get()->getICrugeFieldLoadModelByName($fieldname);
	}
	/*
		retorna una instancia de ICrugeField nueva en blanco
	*/
	public function createEmptyField(){
		return CrugeFactory::get()->getICrugeFieldCreate(CRUGEFIELDTYPE_TEXTBOX);
	}
	/*
		recibe una instancia de ICrugeStoredUser y carga en esta todos los campos personalizados
		de perfil que el administrador ha definido.

		@returns: un array de instancias ICrugeField con el valor (fieldvalue) correspondiente.

		@see loadUserById
	*/
	public function loadUserFields(ICrugeStoredUser $user){
		$user->setFields(CrugeFactory::get()->getICrugeFieldListModels($user));
		return $user->getFields();
	}
	/*
		retorna la lista de campos personalizados, sin referencias a ningun usuario.
		@returns array de ICrugeField (sin valor asignado)
	*/
	public function getUserFields(){
		return CrugeFactory::get()->getICrugeFieldListModels();
	}
	/**
		limpia los campos personalizados.
	*/
	public function clearUserFields(ICrugeStoredUser $user){
		$user->setFields(CrugeFactory::get()->getICrugeFieldListModels($user));
		foreach($user->getFields() as $field)
			$field->setFieldValue("");
		return $user->getFields();
	}
	/*
		retorna el objeto que implementa a ICrugeFieldValue de un campo aplicado a un usuario,
	*/
	public function loadICrugeFieldValue(ICrugeStoredUser $user,ICrugeField $field){
		return CrugeFactory::get()->getICrugeFieldValue($user,$field);
	}
	/*
		obtiene el valor escalar de un campo para un usuario.

		@iduser: mixed.  puede ser el IDUSER o una instancia de ICrugeStoredUser
		@idfield: mixed.  puede ser el FIELDNAME, IDFIELD o una instancia de ICrugeField
	*/
	public function getFieldValue($iduser,$idfield){

		if(is_string($iduser)){
			$u = $this->loadUserById($iduser);
		}else{
			$u = $iduser;
		}

		if($u != null){

			if(is_numeric($idfield)){
				// busca por idfield
				//
				$field = $this->loadFieldById($idfield);
				if($field == null)
					return "";
			}
			else
			{
				// busca por nombre
				//
				if(is_string($idfield)){
					$field = $this->loadFieldByName($idfield);
					if($field == null)
						return "";
				}
				else{
					// asume que es una instancia que implementa a ICrugeField
					$field = $idfield;
				}
			}

			if($field != null){
				$fv = CrugeFactory::get()->getICrugeFieldValue($u,$field);
				if($fv != null)
					return $fv->value;
			}
		}
		return "";
	}

	/*
		funciona como lo haria un CActiveForm::labelEx, pero considerando que estos
		campos aqui indicados no pertenecen al modelo como tal porque son campos definidos
		por el admin.

		lo que se hara aqui es presentar una etiqueta pero con una clase "required" y un
		asterisco para indicar que el campo es requerido si la config del campo asi lo decide.
	*/
	public function getLabelField(ICrugeField $field){

		$r = "";
		$ast = "";
		$text = ucfirst(CrugeTranslator::t($field->longname));
		if($field->required == 1){
			$r = " class='required' ";
			$ast = "<span {$r}>*</span>";
		}

		return "<label {$r}>{$text} {$ast}</label>";
	}

	/*
		retorna el elemento de UI correspondiente a la configuracion del campo personalizado.

		Igualmente recibe el valor correspondiente que el usuario ha ingresado para este campo.

		$model:  es la clase que alojara los datos del formulario, no se usa para nada mas
		de este modo si otro modelo quiere incorporar campos personalizados solo pone "$this" y asi
		los items del form se pondran de acuerdo a la clase indicada.

		$field:  es un campo, instancia de ICrugeField cuyo atributo fieldvalue fue previamente
		establecido. @see loadUserFields (para saber como se carga fieldvalue)

		este metodo es basicamente en las vista:
			usermanagementupdate.php
			registration.php

		@returns Elemento tag de CHtml de acuerdo a la configuracion de $field->fieldtype
		@see loadUserFields (para saber como se carga fieldvalue)
	*/
	public function getInputField($model,ICrugeField $field){

		$className = get_class($model);

		$name = $className."[".$field->fieldname."]";
		$htmlOpt = array(
			 'id'=>$className."_".$field->fieldname
			,'size'=>$field->fieldsize
			,'maxlength'=>$field->maxlength
			,'rows'=>5
			,'cols'=>$field->fieldsize
		);

		// caso listas: Listbox
		// se espera que venga cada valor que se pasara al <option></option>
		// venga en la forma "VALUE, TEXT"
		//
		$arOpt = array();
		if($field->fieldtype == CRUGEFIELDTYPE_LISTBOX){
			$arOpt = CrugeUtil::explodeOptions($field->predetvalue);
			$htmlOpt['rows'] = null;
			$htmlOpt['cols'] = null;
			$htmlOpt['size'] = null;
			$htmlOpt['maxlength'] = null;
		}

		// estos tipos definidos estan en CrugeUserManager

		switch($field->fieldtype){
			case CRUGEFIELDTYPE_TEXTBOX:
				return CHtml::textField($name,$field->getFieldValue(),$htmlOpt)."\n";
			case CRUGEFIELDTYPE_TEXTAREA:
				return CHtml::textArea($name,$field->getFieldValue(),$htmlOpt)."\n";
			case CRUGEFIELDTYPE_BOOLEAN:
				return CHtml::checkBox($name,$field->getFieldValue(),$htmlOpt)."\n";
			case CRUGEFIELDTYPE_LISTBOX:
				return CHtml::dropDownList(
					$name,
					$field->getFieldValue(),
					$arOpt,
					$htmlOpt)."\n";
		}
		return null;
	}

	public function getSearchModelICrugeStoredUser(){
		return CrugeFactory::get()->getNewICrugeStoredUserForSearch();
	}
	public function getSearchModelICrugeSession(){
		return CrugeFactory::get()->getNewICrugeSessionForSearch();
	}
	public function getSearchModelICrugeField(){
		return CrugeFactory::get()->getNewICrugeFieldForSearch();
	}
	/*
		crea una nueva instancia del modelo CrugeLogon bajo el escenario indicado {login o pwdrec}

		CrugeLogon es un modelo (CFormModel) para el formulario de Login y Password Recovery
		que aparte de validar que los datos de ambos fomularios esten correctos tambien
		ayuda al proceso de llamar a Yii::app()->user->login mediante un metodo llamado login().

		basicamente es como el modelo LoginForm que trae Yii por defecto.

		@see CrugeLogon
		returns instancia de CrugeLogon
	*/
	public function getNewCrugeLogon($scenario)
	{
		return new CrugeLogon($scenario);
	}

	/*
		crea una nueva instancia del modelo de autenticacion CrugeUser el cual
		representa a un usuario que quiere iniciar sesion (no es un usuario almacenado)
	*/
	public function getNewCrugeUser($username,$password,$authMode='default'){
		return new CrugeUser($username,$password,$authMode);
	}

	public function getSortFieldNamesForICrugeStoredUser(){
		return CrugeFactory::get()->getICrugeStoredUserSortFieldNames();
	}
	public function getSortFieldNamesForICrugeField(){
		return CrugeFactory::get()->getICrugeFieldSortFieldNames();
	}


	public function loadSessionById($id){
		return CrugeFactory::get()->getICrugeSession($id);
	}

}
