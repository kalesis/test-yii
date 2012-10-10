<?php
/**
 * CrugeStoredUser
 *
 *  Modelo que realiza la persistencia de components.CrugeUser
 *	
 * @property integer $iduser				
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $authkey
 * @property integer $state
 * @property integer $totalsessioncounter
 * @property integer $currentsessioncounter
 * @property string $regdate	fecha de registro
 * @property string $actdate	fecha de activacion
 * @property string $logondate	ultimo login exitoso
 
 	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
 */
class CrugeStoredUser extends CActiveRecord implements ICrugeStoredUser
{
	public $_fields=array();
	public $deleteConfirmation; // required on 'delete'
	public $newPassword;		// declararlo 'safe'
	
	// terminos y condiciones, caso registration,
	public $terminosYCondiciones;
	public $verifyCode;
	
	// establecer a true si se quiere saltar la validacion de captcha.
	// ver acerca de: cruge\components\CrugeUserManager.php::createBlankUser
	public $bypassCaptcha;	
	

	/* es un loadModel de uso multiple. $modo puede ser: 'iduser','username' o 'email' para
		indicar por cual campo se quiere cargar el modelo.
		@returns ICrugeStoredUser
	*/
	public static function loadModel($id,$modo='iduser'){
		return self::model()->findByAttributes(array($modo=>$id));
	}
	/* entrega un array con los nombres de los atributos clave para orden, de primero el userid */
	public static function getSortFieldNames(){
		return array('iduser','username','email','state','logondate');
	}
	public function getStateName(){
		return Yii::app()->user->um->getStateName($this->state);
	}
	/*
		recibe un array de instancias de ICrugeField previamente cargada de valores
	*/
	public function setFields($arFields){
		$this->_fields = $arFields;
	}
	public function getFields(){
		if($this->_fields == null)
			return array();
		return $this->_fields;
	}
	public function setAttributes($values, $safeOnly = true){

		if(count($this->getFields()) > 0){
			$test = __CLASS__.".setAttributes:\n";
			foreach($values as $k=>$v)
				$test .= "[{$k}={$v}]\n";
			$test .="\nparse field values:\n";
			foreach($values as $fieldName=>$value){
				$test .= "{$fieldName}...";
				
				$boolFound=false;
				foreach($this->getFields() as $f)
					if($f->fieldname == $fieldName)
						{ 
							$test .= " found. setfieldvalue:[{$value}]\n";
							$f->setFieldValue($value);
							$boolFound=true;
							break; 
						}
				if($boolFound==false){
					$test .= " [not found]\n";
				}
			}
			Yii::log($test,"info");
		}
		
		parent::setAttributes($values);
	}
	public function validate($attributes = NULL, $clearErrors = true){
		// realiza la validacion normal sobre los atributos de este modelo
		//
		$validateResult = parent::validate();
		
		// ahora realiza la validacion sobre aquellos campos personalizados
		// y copia todos los errores al objeto mayor ($this)
		//
		foreach($this->getFields() as $f)
			if($f->validateField() == false){
				$this->addErrors($f->getErrors());
				$validateResult = false;
			}
			
		return $validateResult;
	}
	public function save($runValidation = true, $attributes = NULL){
		Yii::log(__METHOD__,"info");
		if($this->hasErrors()){
			Yii::log(__METHOD__." return false, has errors.","info");
			return false;
		}
		
		// importante aqui:
		// primero debe guardar el usuario (this) y luego los campos
		// si se hiciera al reves y el escenario fuese 'insert' entonces al crear el CrugeFieldValue // se generaria un error porque el user->iduser no existiria aun.
		//
		$ok = parent::save();
		$this->saveFields();
		
		Yii::log(__METHOD__." returns: [".$ok."]","info");
		return $ok;
	}
	public function saveFields(){
		foreach($this->getFields() as $f)	
			{
				// buscar el objeto ICrugeFieldValue, darle valores y guardarlo
				$crugeFieldValueInst = Yii::app()->user->um->loadICrugeFieldValue($this,$f);
				$boolOk = false;
				if($crugeFieldValueInst != null){
					$crugeFieldValueInst->value = $f->getFieldValue();
					$boolOk = $crugeFieldValueInst->save();
				}
				Yii::log("\n".__METHOD__." \nfieldname='".$f->fieldname."'\nfieldvalue='".$f->getFieldValue()
					."'\n boolOk=[".$boolOk."]\ncrugeFieldValueInst=[".($crugeFieldValueInst==null ? 'null' : 'not null')."]\n\n","info");
			}
	}
	
	/**
		@retuns string nombre de usuario (para login).
	*/
	public function getUserName(){
		return $this->username;
	}
	public function getEmail(){
		return $this->email;
	}
	public function tableName()
	{
		return CrugeUtil::getTableName('user');
	}
	public function getPrimaryKey(){
		return $this->iduser;
	}
	public static function listModels($param=array()){
		return self::model()->findAllByAttributes($param);
	}
	public function getUpdateUrl(){
		return 'index.php?r=test'.$this->getPrimaryKey();
	}
	
	
	
	
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return CrugeStoredUser the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	

	/**  hay un escenario llamado 'internal', que es puesto por CrugeUserManager::save()
	 *   para poder guardar atributos especificos sin ser afectado por las reglas para formularios
	 *
	 *
	 *
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('username','match','pattern'=>'/^[a-z0-9_-]{3,15}$/'
				, 'message'=>CrugeTranslator::t("nombre de usuario no es valido")),
			array('username,email', 'required'),
			
			
			array('newPassword','safe','on'=>'update'),
			array('newPassword','required','on'=>'insert, manualcreate'),
			array('newPassword', 'length', 'min'=>6, 'max'=>20),
			array('newPassword','match'
				,'pattern'=>'/^[a-zA-Z0-9@#$%]{6,20}$/'
				, 'message'=>CrugeTranslator::t("la clave solo puede tener digitos letras o los simbolos @#$% con longitud entre 6 y 20 caracteres")),
			
			array('username, password', 'length', 'max'=>45),
			array('state', 'numerical', 'integerOnly'=>true),
			array('authkey', 'length', 'max'=>100),
			array('email', 'email'),
			array('email', 'length', 'max'=>100),
			
			array('username,email', 'validate_unique'),
			
			array('deleteConfirmation','required','on'=>'delete'),
			array('deleteConfirmation','compare','compareValue'=>'1'
				,'on'=>'delete', 'message'=>CrugeTranslator::t("por favor confirme con la casilla de chequeo")),
			
			
			array('terminosYCondiciones','required'	
				,'requiredValue'=>'1'
				,'on'=>'insert'
				,'message'=>CrugeTranslator::t('por favor marque esta casilla para indicar que comprende los terminos'),
				),
				
				
			array('verifyCode', $this->_getCaptchaRule(), 'on'=>'insert',
					'message'=>CrugeTranslator::t('El codigo de seguridad es requerido'),
				),
			array('verifyCode', 'captcha',
					'on'=>'insert',
					'allowEmpty'=>true,
					'message'=>CrugeTranslator::t('El codigo de seguridad no es correcto'),
				),	
			
			array('iduser, username, email, state, logondate','safe','on'=>'search'),
			
		);
	}
	
	/**
		al establecer $_crugeStoredUser->bypassCaptcha = true; 
		entonces el captcha no sera tomado en cuenta.
		
		esta funcion es util cuando se quiere crear un nuevo usuario de cruge por la via del API.
	*/
	private function _getCaptchaRule(){
		if(Yii::app()->user->um->getDefaultSystem()->getn('registerusingcaptcha') == 1){
			// el administrador decidio pedir captcha para registrar los usuarios,
			// 	pero quiza el flag bypassCaptcha este activo.
			if($this->bypassCaptcha == true){
				// captcha es requerido, pero sera no sera tomado en cuenta.
				$this->verifyCode = null;
				return 'safe';
			}else
			return 'required'; // captcha es requerido
		}
		else 
		{
			// el administrador ha deshabilitado el uso de captcha.
			$this->verifyCode = null;
			return 'safe';
		}
	}
	
	
	public function validate_unique($att, $params){
		$model = self::model()->findByAttributes(array($att=>$this[$att]));
		if($model != null){
			$duptext = CrugeTranslator::t("El '".$att."' indicado ya esta en uso");
			if($this->scenario == 'insert'){
				$this->addError($att,$duptext);
				return;
			}
			if($this->scenario == 'update'){
				if($this->iduser != $model->iduser)
					$this->addError($att,$duptext);
				return;
			}
		}
	}	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'sessions' => array(self::HAS_MANY, 'crugesession' , 'iduser'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'idusuario' => ucfirst(CrugeTranslator::t('usuario#')),
			'username' => ucfirst(CrugeTranslator::t('username')),
			'email' => ucfirst(CrugeTranslator::t('correo')),
			'password' => ucfirst(CrugeTranslator::t('clave')),
			'authkey' => ucfirst(CrugeTranslator::t('llave de autenticacion')),
			'state' => ucfirst(CrugeTranslator::t('estado de la cuenta')),
			'newPassword' => ucfirst(CrugeTranslator::t('clave')),
			'deleteConfirmation' => ucfirst(CrugeTranslator::t('confirmar eliminacion')),
			'regdate' => ucfirst(CrugeTranslator::t('registrado')),
			'actdate' => ucfirst(CrugeTranslator::t('activado')),
			'logondate' => ucfirst(CrugeTranslator::t('ultimo acceso')),
			'terminosYCondiciones' => ucfirst(CrugeTranslator::t('comprendo y acepto, por favor registrarme')),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		
		$criteria->compare('iduser',$this->iduser);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('state',$this->state);
		$criteria->compare('logondate',$this->logondate);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array('iduser'=>true),
			),
		));
	}
}