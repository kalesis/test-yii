<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'TestYii App',
	
	'language'=>'es',
	'sourceLanguage'=>'es',
	
	// preloading 'log' component
	'preload'=>array('log','bootstrap'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.modules.cruge.components.*',
		'application.modules.cruge.extensions.crugemailer.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'123456',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('*'),
			/*'generatorsPath' => array(
				'ext.bootstrap.gii.bootstrap'
			),*/
		),
		'cruge'=>array(
			'tableprefix'=>'seg_',
			// usar : 'default' o 'authdemo'
			'availableAuthMethods'=>array('default'),
			
			'availableAuthModes'=>array('username','email'),
			'baseUrl'=>'http://testyii.local/',
			
			//@TODO set a false despues de instalar
			'debug'=>false,
			'rbacSetupEnabled'=>true,
			'allowUserAlways'=>true,
			
			//@TODO en false mientras se instala
			'useEncryptedPassword' => false,
			
			//@FIXED A donde enviar al usuario tras iniciar sesion, cerrar sesion o al expirar la sesion.
			// esto va a forzar a Yii::app()->user->returnUrl cambiando el comportamiento estandar de Yii
			// en los casos en que se usa CAccessControl como controlador, ejemplo:
			//      'afterLoginUrl'=>array('/site/welcome'),
			//      'afterLogoutUrl'=>array('/site/page','view'=>'about'),
			'afterLoginUrl'=>null,
			'afterLogoutUrl'=>null,
			'afterSessionExpiredUrl'=>null,
			
			// manejo del layout con cruge.
			'loginLayout'=>'//layouts/main',
			'registrationLayout'=>'/layouts/main',
			'activateAccountLayout'=>'//layouts/main',
			'editProfileLayout'=>'//layouts/main',
			// en la siguiente puedes especificar el valor "ui" o "column2" para que use el layout
			// de fabrica, es basico pero funcional.  si pones otro valor considera que cruge
			// requerira de un portlet para desplegar un menu con las opciones de administrador.
			'generalUserManagementLayout'=>'ui',
			
		),
		
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'bootstrap' => array(
			'class' => 'ext.bootstrap.components.Bootstrap',
			'responsiveCss' => true,
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		/*'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),*/
		// uncomment the following to use a MySQL database
		
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testyii',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '123456',
			'charset' => 'utf8',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
		// Respecto a CRUGE
		'user'=>array(
			'allowAutoLogin'=>true,
			'class' => 'application.modules.cruge.components.CrugeWebUser',
			'loginUrl' => array('/cruge/ui/login'),
		),
		
		'authManager' => array(
			'class' => 'application.modules.cruge.components.CrugeAuthManager',
		),
		
		'crugemailer'=>array(
			'class' => 'application.modules.cruge.components.CrugeMailer',
			'mailfrom' => 'c.acosta@icprojects.pe',
			'subjectprefix' => 'Enviado por - ',
			'debug' => false,
			'throwsAnExceptionIfMailFails'=>false
		),
		
		'format' => array(
			'datetimeFormat'=>"d M, Y h:m:s a",
		),
		
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'soporte@icprojects.pe',
	),
);