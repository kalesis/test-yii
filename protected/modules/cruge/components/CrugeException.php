<?php 
/**	CrugeException

	centraliza la emision de excepciones, ayudando a traducir los mensajes usando CrugeTranslator

	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/
*/
class CrugeException extends CHttpException {
	public $classParent;
	public $extra;
	public $code;
	public function __construct($message,$code=500,$extra=""){
		parent::__construct($code,$message);
		$this->code = $code;
		$this->extra = $extra;
	}
	public function __toString() {
		
		//return $this->classParent . ": [{$this->code}]: ".CrugeTranslator::t($this->message)."\n".$extra;
		
		return CrugeTranslator::t($this->message)."<br/>".$this->code;
    }
}