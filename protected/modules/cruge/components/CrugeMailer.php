<?php 
	/** CrugeMailer
	
		Provee la creacion de correos electronicos basados en esquema de vistas,
		ayudando ademas a la centralizacion del despacho de los correos y ayudando
		tambien a "componentizar".
		
		instalacion: en config/main
		
			'crugemailer'=>array(
				'class' => 'application.modules.cruge.extensions.crugemailer.CrugeMailer',
				'mailfrom' => 'christiansalazarh@gmail.com',
				'subjectprefix' => 'CrugeMailer - ',
			),
		
		uso:
			$juanperez = new Usuario();					// tu modelo
			$juanperez->email = 'juanperez@gmail.com';	// argumentos de prueba
			$juanperez->nombres = "Juan Perez";			// 
			
			Yii::app()->crugemailer->enviarUnCorreoA($juanperez,"asunto");
		
		entonces, crear un metodo en CrugeMailer:
		
			public function enviarUnCorreoA($persona,$asunto){
				$this->sendemail($persona->email,self::t($asunto)
					,$this->render('enviarUnCorreoA',array('data'=>$persona))
				);
			}
			
			la llamada a $this->render con argumento 'enviarUnCorreoA' invocara
			tanto un layout con nombre 'mailer' como una vista llamada 'enviarUnCorreoA'
			ambos ubicados en la carpeta views/layout y views/mailer respectivamente.
			
		si se ejecuta bajo un modulo, entonces sobreescribir el metodo init y
		hacer una llamada a setModule(con el modulo en cuestion apuntado aqui);
			
		y para darle formato al correo:

			deben existir dos archivos:
				views/layout/mailer.php , tal cual como se usan los layouts en Yii,
			y por cada correo a enviar se puede crear una vista en:
				views/mailer/enviarUnCorreoA.php (siguiendo el ejemplo de arriba)
			
		
		para controlar el despacho de los correos: 
			editar a CrugeMailerBase.php o crear un metodo sendemail con los mismos argumentos para que se ajuste a las necesidades.
		
		@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
		http://www.yiiframeworkenespanol.org/licencia
	*/
	class CrugeMailer extends CrugeMailerBase implements ICrugeMailer {
	
		public $debug = false;
		public $throwsAnExceptionIfMailFails = false;
	
		// debido a que es un componente.
		public function init(){
			parent::init();
			$this->setModule(CrugeUtil::config());
		}
	
		// un traductor
		public function t($text){
			return CrugeTranslator::t($text);
		}
		
		public function sendPasswordTo(ICrugeStoredUser $userInst,$notEncryptedPassword){
			$this->sendEmail($userInst->email,self::t("su clave clave de acceso")
				,$this->render('sendpasswordto'
				,array('model'=>$userInst,'password'=>$notEncryptedPassword))
			);
		}		
		
		
		public function sendRegistrationEmail(ICrugeStoredUser $userInst,$notEncryptedPassword){
			$this->sendEmail($userInst->email,self::t("activacion de su cuenta")
				,$this->render('sendregistrationemail'
				,array('model'=>$userInst,'password'=>$notEncryptedPassword))
			);
		}
		
		public function sendWaitForActivation(ICrugeStoredUser $userInst,$notEncryptedPassword)
		{
			$this->sendEmail($userInst->email,self::t("ha solicitado registrarse, espere por activacion.")
					,$this->render('sendwaitforactivation'
					,array('model'=>$userInst,'password'=>$notEncryptedPassword))
			);
		}
		
		/**
		 * 
		 * @param array $form
		 */
		public function sendMailContact( $form )
		{
			//CVarDumper::dump($form);
			
			//$this->sendEmail($formContac['email'],$formContac['subject'],$formContac['body']);
			$this->sendEmail($form->email,
					$form->subject
					,$this->render('sendmailcontact',array('model'=>$form))
			);
		}
		
		
		/*
		 este metodo se coloca aqui para que puedas personalizar el envio de correo
		 usando tu propia metodo, si quieres usar el metodo por defecto (mail) entonces
		 simplemente llamas a parent::sendEmail.  
		*/
		public function sendEmail($to,$subject,$body) 
		{
			$_subject = $this->subjectprefix.$subject;
			$mailer = Yii::createComponent('application.extensions.mailer.EMailer');
			$mailer->Host = '';
			$mailer->IsSMTP();
			
			$mailer->SMTPDebug  = 1;                     // Habilita información SMTP (opcional para pruebas)
			// 1 = errores y mensajes
			// 2 = solo mensajes
			$mailer->SMTPAuth   = true;                  // Habilita la autenticación SMTP
			$mailer->SMTPSecure = "ssl";                 // Establece el tipo de seguridad SMTP
			$mailer->Host       = "smtp.gmail.com";      // Establece Gmail como el servidor SMTP
			$mailer->Port       = 465;                   // Establece el puerto del servidor SMTP de Gmail
			$mailer->Username   = 'user@gmail.com';  // Usuario Gmail
			$mailer->Password   = 'clave';   // Clave Usaurio Gmail
			$mailer->From = $this->mailfrom;
			$mailer->FromName = 'Mail Web Contact';
			$mailer->IsHTML(true);
			//$mailer->AddReplyTo('wei@example.com');
			$mailer->AddAddress($to);
			
			$mailer->CharSet = 'UTF-8';
			$mailer->Subject = $_subject;
			$mailer->Body = $body;
			$mailer->Send();
		}
	}
?>
