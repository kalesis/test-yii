CRUGE
-----

Extensión para el Control de Usuarios y Roles.

[**Ir al repositorio de Cruge Bitbucket**](https://bitbucket.org/christiansalazarh/cruge "ir al repositorio en bitbucket")

[visita mi blog](http://trucosdeprogramacionmovil.blogspot.com/ "visita mi blog")

[Comunidad de Yii Framework en Español](http://yiiframeworkenespanol.org/ "Comunidad de Yii Framework en Español")


[PROYECTO BASICO DE DEMOSTRACION](https://bitbucket.org/christiansalazarh/crugeholamundo/ "PROYECTO BASICO DE DEMOSTRACION")


![screenshots][1]

##Entorno en donde se ha probado.

* PHP Version 5.2.6, 5.4.4, 5.4.5

* Yii Framework 1.10,  1.11, 1.12, 1.13

* Apache/2.0.58

##Que es Cruge ?

 Cruge te permite administrar y controlar de forma muy eficiente y segura a tus usuarios y los roles que ellos deban tener en tu aplicacion.

 Cruge tiene una alta Arquitectura OOP, basada en interfaces, lo que ayuda enormemente a usarla sin modificar en lo absoluto su propio core. Si necesitas cambiar de ORDBM, cruge lo permite.  Si necesitas extender el funcionamiento de autenticacion para admitir nuevos metodos tambien lo permite mediante la implantacion de filtros de autenticacion, incluso dispones ademas de filtros insertables para controlar el otorgamiento de una sesion a un usuario y finalmente para controlar los registros y actualizaciones de perfil de tus usuarios. Todo eso sin tocar en lo absoluto el core de Cruge.

 Cruge es un API, que incluye una interfaz de usuario predeterminada con el objeto que puedas usar el sistema como viene ahorrandote mucho tiempo. Esta interfaz hace uso del API de forma estricta, es decir, no hay "dependencias espaguetti" las cuales son las primeras destructoras de todo software.

 La arquitectura que tu usarás en Cruge es asi:

	[Tu Aplicacion]--->[Yii::app()->user]			acceso a funciones muy basicas de autenticacion
	[Tu Aplicacion]--->[Yii::app()->user->ui]		provee enlaces a funciones de la interfaz
	[Tu Aplicacion]--->[Yii::app()->user->um]		provee acceso al control de los usuarios
	[Tu Aplicacion]--->[Yii::app()->user->rbac]		provee acceso a las funciones de RBAC

internamente esta arquitectura dentro de Cruge es asi:

	[Tu Aplicacion]--->[Cruge]--->[API]---->[Factory]---->[modelos]

esto significa, que aun dentro de Cruge las dependencias son estrictamente organizadas, es decir, no verás en ningun lado que el API vaya a instanciar a un modelo cualquiera, si eso fuera asi estariamos hablando de otra "extension" espaguetti, como aquellas que solo le funcionan a su creador, o que en el mejor de los casos, funcionan...pero manipulandoles el core.

 Cruge es una extension en todo lo ancho de la palabra, realmente extiende las funciones basicas
 de manejo de usuario de Yii Framework, incorporando mas funciones en los paquetes originales.

 La interfaz de usuario de Cruge es opcional, con esto quiero decirte que puedes usar Cruge en modo API, para lo cual debes conocer el modelo con detalles, aunque es muy intuitivo y de referencias cortas, para hacerlo entendible.


---

##Instalación

Primero voy a asumir que Cruge ha sido descargado a tu carpeta:

	/protected/modules/cruge/

puedes descargar cruge directamente desde un ZIP, o mediante un comando GIT como: git clone [URL DE GIT].

En el archivo de configuración de tu aplicacion (config/main.php) deberas colocar lo siguiente:

	1.	dentro de 'import' agregar:
			'application.modules.cruge.components.*',
			'application.modules.cruge.extensions.crugemailer.*',

	2.	dentro de 'modules' agregar:
			'cruge'=>array(
				'tableprefix'=>'cruge_',

				// para que utilice a protected.modules.cruge.models.auth.CrugeAuthDefault.php
				//
				// en vez de 'default' pon 'authdemo' para que utilice el demo de autenticacion alterna
				// para saber mas lee documentacion de la clase modules/cruge/models/auth/AlternateAuthDemo.php
				//
				'availableAuthMethods'=>array('default'),

				'availableAuthModes'=>array('username','email'),
				'baseUrl'=>'http://coco.com/',

				 // NO OLVIDES PONER EN FALSE TRAS INSTALAR
				 'debug'=>true,
				 'rbacSetupEnabled'=>true,
				 'allowUserAlways'=>true,

				// MIENTRAS INSTALAS..PONLO EN: false
				// lee mas abajo respecto a 'Encriptando las claves'
				//
				'useEncryptedPassword' => false,

				// a donde enviar al usuario tras iniciar sesion, cerrar sesion o al expirar la sesion.
				//
				// esto va a forzar a Yii::app()->user->returnUrl cambiando el comportamiento estandar de Yii
				// en los casos en que se usa CAccessControl como controlador
				//
				// ejemplo:
				//		'afterLoginUrl'=>array('/site/welcome'),  ( !!! no olvidar el slash inicial / )
				//		'afterLogoutUrl'=>array('/site/page','view'=>'about'),
				//
				'afterLoginUrl'=>null,
				'afterLogoutUrl'=>null,
				'afterSessionExpiredUrl'=>null,

				// manejo del layout con cruge.
				//
				'loginLayout'=>'//layouts/main',
				'registrationLayout'=>'//layouts/main',
				'activateAccountLayout'=>'//layouts/main',
				'editProfileLayout'=>//puedes '//layouts/main',
				// en la siguiente puedes especificar el valor "ui" o "column2" para que use el layout
				// de fabrica, es basico pero funcional.  si pones otro valor considera que cruge
				// requerirá de un portlet para desplegar un menu con las opciones de administrador.
				//
				'generalUserManagementLayout'=>'ui',
			),

	3.	dentro de 'components' agregar:
	        //
			//  IMPORTANTE:  asegurate de que la entrada 'user' (y format) que por defecto trae Yii
			//               sea sustituida por estas a continuación:
			//
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
				'mailfrom' => 'email-desde-donde-quieres-enviar-los-mensajes@xxxx.com',
				'subjectprefix' => 'Tu Encabezado del asunto - ',
				'debug' => true,
			),
			'format' => array(
				'datetimeFormat'=>"d M, Y h:m:s a",
			),

	4.	crea las tablas requeridas en la base de datos de tu aplicacion, usa el script:

		<tuaplicacion>/protected/modules/cruge/data/cruge-data-model.sql

		aqui se va a crear automaticamente un usuario para que puedas comenzar, toma nota:

<div style='color: blue;'>
usuario=<b>admin</b>
clave=<b>admin</b>
</div>

	5.  Configura el menu de tu aplicacion para que incopore a Cruge, para ello
	edita tu archivo:
		/protected/views/layouts/main.php
	y sustituye el componente CMenu por el que te doy a continuacion.

		<?php $this->widget('zii.widgets.CMenu',array(
			'items'=>array(
				array('label'=>'Home', 'url'=>array('/site/index')),
				array('label'=>'About', 'url'=>array('/site/page', 'view'=>'about')),
				array('label'=>'Contact', 'url'=>array('/site/contact')),
				array('label'=>'Administrar Usuarios'
					, 'url'=>Yii::app()->user->ui->userManagementAdminUrl
					, 'visible'=>!Yii::app()->user->isGuest),
				array('label'=>'Login'
					, 'url'=>Yii::app()->user->ui->loginUrl
					, 'visible'=>Yii::app()->user->isGuest),
				array('label'=>'Logout ('.Yii::app()->user->name.')'
					, 'url'=>Yii::app()->user->ui->logoutUrl
					, 'visible'=>!Yii::app()->user->isGuest),
			),
		)); ?>

---

##Uso básico de Cruge

##Obtener un usuario:

	Con Yii::app()->user->um tienes acceso al API de usuarios de Cruge, la cual es:
		cruge\components\CrugeUserManager.php

	ejemplos:

	 <?php
		// POR SU USERNAME o EMAIL
		$usuario = Yii::app()->user->um->loadUser('admin@gmail.com',true);
		echo $usuario->username;
		// true: es para indicar que cargue los valores de los campos personalizados.
		// por defecto es : false.
	 ?>

	 <?php
		// POR SU ID
		$usuario = Yii::app()->user->um->loadUserById(123,true);
		echo $usuario->username;
		// true: es para indicar que cargue los valores de los campos personalizados.
		// por defecto es : false.
	 ?>


##Manejar un usuario usando el API de Cruge

A veces es necesario crear un usuario desde nuestro sistema y no solo usando la interfaz de usuario que Cruge provee, por esto Cruge provee un simple metodo para crear un nuevo usuario:

	<?php
		public function actionAjaxCrearUsuario(){
			// asi se crea un usuario (una nueva instancia en memoria volatil)
			$usuarioNuevo = Yii::app()->user->um->createBlankUser();

			$usuarioNuevo->username = 'username1';
			$usuarioNuevo->email = 'username1@gmail.com';
			// la establece como "Activada"
			Yii::app()->user->um->activateAccount($usuarioNuevo);

			// verifica para no duplicar
			if(Yii::app()->user->um->loadUser($usuarioNuevo->username) != null)
				{
					echo "El usuario {$usuarioNuevo->username} ya ha sido creado.";
					return;
				}

			// ponerle una clave
			Yii::app()->user->um->changePassword($usuarioNuevo,"123456");

			// guarda usando el API, la cual hace pasar al usuario por el sistema de filtros.
			if(Yii::app()->user->um->save($usuarioNuevo)){

				echo "Usuario creado: id=".$usuarioNuevo->primaryKey;
			}
			else{
				$errores = CHtml::errorSummary($usuarioNuevo);

				echo "no se pudo crear el usuario: ".$errores;
			}
		}
	?>

##Campos Personalizados:

	<?php
		echo "Su nombre es: ";
		echo Yii::app()->user->getField('nombre');

		// en el caso del email use:
		// (esto es porque cruge incopora el metodo getEmail a Yii::app()->user )
		echo "Su email es:";
		echo Yii::app()->user->email;

		// para acceder al objeto usuario (el CrugeStoredUser)
		//
		$usuario = Yii::app()->user->user;

		// para listar todos los campos personalizados del usuario indicado:
		//
		foreach(Yii::app()->user->user->fields as $campo)
			echo "<p>campo: ".$campo->longname." es: ".$campo->fieldvalue;"</p>";

	?>

##Verificar un permiso de acceso:
		aqui se pretende verificar si el usuario activo tiene asignado el rol 'admin':

		<?php
			if(Yii::app()->user->checkAccess('admin')){
				...
			}
		?>

##El menu Variables del Sistema

Este menu te permite controlar algunos aspectos fundamentales basicamente acerca del modo en como se registran los usuarios en el sistema, por ejemplo:

	1. Detener Sistema
		si esta activada no se permitira iniciar sesion o usar el sistema en general.

	2. No Admitir Nuevas Sesiones
		solo bloquea el login, es decir nuevas sesiones de usuario.

	3. Minutos de Duracion de la Sesion *
		el tiempo en minutos en que una sesion expira.

	4. Registrarse usando captcha
		si esta activada presenta un captcha al momento de registrar a un nuevo usuario.

	5. Activacion del usuario registrado
		opciones para aplicar al nuevo usuario registrado, puedes activarlo inmediatamente, o mediante
		activacion manual por parte de un administrador, o mediante un correo de verificacion.

	6. Asignar Rol a usuarios registrados
		es el rol que tu quieres que se le asigne por defecto al usuario recien registrado.

	7. Ofrecer opción de Registrarse en pantalla de Login
		habilita el link de "registrarse" en la pantalla de login.
		para que esta opcion funcione necesitas actualizar tu base de datos con el siguiente script sql:
			"alter table cruge_system add registrationonlogin integer default 1;"

	8. Registrarse usando terminos
		si lo activas, el usuario que se va a registrar debe aceptar los terminos que tu indiques.

	9. Etiqueta
		si la opcion anterior esta activa se le hara una pregunta, aqui puedes escribir esa pregunta,
		por ejemplo: "Por favor lea los terminos y condiciones y aceptelos para proceder con su registro"

	10. Terminos y Condiciones de Registro
		El texto de las condiciones para registrarse, solo aplica si la opcion de "registrarse usando terminos" esta activada.

##Acceder a las variables del sistema:

		<?php
			if(Yii::app()->user->um->getDefaultSystem()->getn('registerusingcaptcha')==1){
				...
			}
		?>

##Acceder a la Interfaz de Usuario de Cruge (usando enlaces):
	Puedes invocar al API UI de Cruge para acceder a los enlaces:

		<?php echo Yii::app()->user->ui->getLoginLink('iniciar sesion'); ?>

	o simplemente:

		<?php echo Yii::app()->user->ui->loginLink; ?>

	La lista de enlaces disponibles es:

	<?php echo Yii::app()->user->ui->loginLink; ?>
	<?php echo Yii::app()->user->ui->logoutLink; ?>
	<?php echo Yii::app()->user->ui->passwordRecoveryLink; ?>
	<?php echo Yii::app()->user->ui->userManagementAdminLink; ?>
	<?php echo Yii::app()->user->ui->registrationLink; ?>

	**lo que no debes hacer es: hacer render de vistas internas de cruge, asi no funciona esto
	porque le quitas la programacion cuando tu mismo renderizas las vistas**

##Personalizando Cruge con el uso de Layouts

Cruge te permite que su interfaz de usuario predeterminada pueda ajustarse a tu sitio web usando lo que en Yii se conoce como Layouts.

Por ejemplo, quieres que el formulario de registro de nuevo usuario se presente en un esquema de diseño distinto al que yii trae por defecto, entonces tu podrias crear un nuevo layout que se ajuste a tus necesidades y luego indicarle a Cruge mediante la configuracion del componente cual seria ese layout a usar cuando un usuario quiera registrarse, asi:

	<?php
		'components'=> array(
			..otros ajustes aqui...
			'loginLayout'=>'//layouts/bootstrap',
			'registrationLayout'=>'//layouts/bootstrap',
			'activateAccountLayout'=>'//layouts/bootstrap',
			'generalUserManagementLayout'=>'//layouts/bootstrap',
		),
	?>

Te cuidado especial con "generalUserManagementLayout": este es un layout especial, porque las funciones de administracion de usuarios requieren un Portlet para presentar las opciones administrativas, por defecto Cruge apunta este valor a: "ui", el cual es el nombre de un layout prefabricado que ya trae un Portlet, practicamente idendico al que Yii trae por defecto llamado //layouts/column2.

El Layout para UI de Cruge por defecto es:

		tuapp/protected/modules/cruge/views/layouts/ui.php

En este layout (ui.php) hay un Portlet, que será llenado con los items de administracion en linea de Cruge, estos items salen del modulo UI de Cruge, el cual es accesible usando:

			Yii::app()->user->ui->adminItems

##Presentando el menu de administracion de usuarios

Este menu ya viene listo en cruge, trae todos los items para que te evites el trabajo de ir
a CrugeUi a ver cuales son los links.

Para acceder al array de menu items lo haces desde:

	<?php
		$items = Yii::app()->user->ui->adminItems;

		// items sera una array listo para insertar en CMenu, BootNavbar o similares.
	?>

Si usas bootstrap, puedes hacerlo asi:

	<?php $this->widget('bootstrap.widgets.BootNavbar', array(
		'fixed'=>false,
		'brand'=>"Tu App",
		'items'=>array(
			array(
				'class'=>'bootstrap.widgets.BootMenu',
				'items'=>array(Yii::app()->user->ui->adminItems),
			),
		),
	)); ?>


##Tras iniciar sesion, cerrar sesion o cuando la sesion expire quiero ir a una pantalla especifica. Como hacerlo ?

En la configuracion principal (tuproyecto/protected/config/main.php) , puedes colocar las URL en las siguientes variables
en el modulo Cruge:

	<?php
		'afterLoginUrl'=>array('/site/welcome'),  ( no olvidar el slash inicial "/" sino la url no funcionara )
		'afterLogoutUrl'=>array('/site/page','view'=>'about'),
		'afterSessionExpiredUrl'=>null,
	?>

##Funcionamiento de Login, Logout, Sesiones.


Lo primero que debes saber es que Cruge tiene un 'filtro para otorgar sesiones' y un 'filtro de autenticacion' estos funcionan asi:

Primero se pasa por el filtro de autenticacion, el cual le da sentido a 'Yii::app()->user->getUser()', luego el filtro de sesión verifica si el sistema esta apto para recibir una nueva sesión, quiza esté en mantenimiento o quiza no, por tanto es el filtro de sesion quien ahora entra en juego.

Una vez que el filtro de sesion determina que se puede dar una sesion a 'juan perez', entonces se le crea una sesion y se llama a un evento llamado 'onLogin' de la clase 'cruge.models.filters.DefaultSessionFilter'.

Este evento de onLogin es quien establece el valor a

	<?php

		Yii::app()->user->returnUrl

	?>

el cual es procesado por UiController para redirigir el browser a una pagina que tu indicas.

Ahora, importante, cuando tu haces logOff manualmente, o si por alguna razón tú usando el api estandar de Yii haces una llamada a

	<?php

		Yii::app()->user->logout()

	?>

verás que tambien serás redirigido a la URL que hayas especificado en 'afterLogoutUrl', la razón de esto es simple:

Cruge es una extensión real del paquete de autenticación estándar de Yii Framework, por tanto para ti es transparente si haces logout o login a mano o de forma automática.



##Tras iniciar sesion quiero ir a una pantalla especifica. Como hacerlo ? (METODO ALTERNO RESPETANDO a CAccessControl).

Supongamos que quieres que tras iniciar sesion exitosamente con Cruge el usuario sea
redirigido al actionBienvenido de siteController (index.php?r=site/bienvenido).

Pues bien el metodo que aqui describo es algo estandar para Cruge o para Yii en general, no es nada nuevo.

1. en siteController (en el controller de tu gusto) creas un action el cual desplegara la pagina
que solo vera aquel usuario que haya iniciado sesion exitosamente.

		<?php
			public function actionBienvenido(){
				$this->render('bienvenido');
			}
		?>

2. en siteController usas el filtro accessControl y los rules (que vienen de caja en Yii), asi:

		<?php
			public function filters()
			{
				return array(
					'accessControl',
				);
			}
			public function accessRules()
			{
				return array(
					array('allow',
						'actions'=>array('index','contact','captcha'),
						'users'=>array('*'),
					),
					array('allow',
						'actions'=>array('bienvenido'),
						'users'=>array('@'),
					),
					array('deny',  // deny all users
						'users'=>array('*'),
					),
				);
			}
		?>

	con esto le estas diciendo a tu aplicacion que para el action "site/bienvenido" se requiere
	que el usuario deba haber iniciado sesion exitosamente ( con cruge o con yii tradicional,
	ambos funcionan por la misma via de autenticacion, por eso y mas Cruge es una extension real y
	no solo un monton de codigo raro ).

	de este modo, si un usuario invitado presiona el enlace "login" entonces tu lo envias
	a site/bienvenido, si no ha iniciado sesion se le pediran credenciales y luego se le enviara
	a la vista site/bienvenido. por tanto el paso 3 es requerido, a continuacion:

3. finalmente sustituye tu enlace a login por un enlace a site/bienvenido.


Que sucedera ?
(podriamos dibujar esto como un diagrama de secuencia en UML)

a) Tu invitado visita tu website (no ha iniciado sesion aun por eso es un invitado) y
sigue el enlace 'login' o 'iniciar sesion' que tu has provisto (y que apunta a site/bienvenido como
dice el paso 3).

b) Tu invitado sera redirigido automaticamente a "index.php?r=cruge/ui/login" (o a la url de login del
sistema que este registrado para autenticar, en este caso Cruge), esto debido al "accessControl"
que implementaste en el paso 2.

c) Luego tras iniciar sesion exitosamente sera redirigido automaticamente a "index.php?r=site/bienvenido",
(funciona asi debido a que en el paso 2 al detectarse que no se ha iniciado sesion entonces se establecio el valor de returnUrl a "site/bienvenido", por tanto cuando Cruge o Yii estandar hacen un login correcto redirigen a tu usuario a la direccion que tenga almacenada en returnUrl, en este caso site/bienvenido)

Como has visto Cruge es un sistema orquestado para trabajar en conjunto con el actual sistema
estandar de autenticacion de Yii, por eso como dije antes es una extension: porque extiende la funcionalidad
basica de Yii a un nivel mas alto.

##Usando RBAC


RBAC es el sistema de control de acceso basado en roles (por sus siglas en ingles).  Todo el mecanismo RBAC puede ser manejado mediante la interfaz (UI) de Cruge, o mediante su API. Las dos modalidades para usar en este mecanismo son:

1. Consulta Manual de Permisos.
Es basicamente el mismo mecanismo que provee Yii, pero en Cruge se ha ampliado un poco mas. Para usar este mecanismo: en cualquier parte de tu codigo fuente puedes poner lo siguiente:

	<?php
		if(Yii::app()->user->checkAccess('puede_ver_menu_sistema')) { ...mostar menu sistema... }
	?>

2. Consulta Automatizada segun controller/action.
Este mecanismo es muy util, porque permite controlar el acceso a tus controllers/actions de forma totalmente automatizada y controlada mediante la UI de Cruge.   Para usar este mecanismo, necesitarás incluir en tu Controller (cualquiera que tu uses y que desees controlar a nivel de permisos) el siguiente codigo:

	<?php
		..cuerpo de tu controladora...

		public function filters()
		{
			return array(
				array('CrugeAccessControlFilter'),
			);
		}

		..cuerpo de tu controladora...
	?>

Al usar CrugeAccessControlFilter estas permitiendo que Cruge controle el acceso tanto al controller en general como al action especifico.

Ejemplo:

	Un usuario cualquiera, incluso un invitado, intenta acceder a la siguiente URL:

		index.php?r=empleado/vernomina.

	pues bien, Cruge verificara dos cosas:

		a) el acceso a la controladora: 'Empleado'.

		b) el acceso al action 'Vernomina'.

	lo hara de esta forma:

		a) verifica si el usuario (aun invitado) tiene asignada la operacion: 'controller_empleado'

		b) verifica si el usuario (aun invitado) tiene asignada la operacion: 'action_empleado_vernomina'

	si ambas condiciones se cumplen (a y b) entonces tendrá acceso al action.

Si tu quieres denegar el total acceso a un controller simplemente no le asignas al usuario la operacion que tenga el nombre del controller antecedido de la palabra 'controller_'.

Si tu quieres denegar el acceso a un action de un controller simplemente no le asignas al usuario la operacion que tenga el nombre del action: 'action_nombrecontroller_nombreaction'.

##Programacion del RBAC: diferencias con otras extensiones.

En el caso de Cruge, la programacion de RBAC no se hace "asumiendo que un usuario va pasar por ahi.."
(modo Yii Rights). En cambio en Cruge, debes "tratar de hacer pasar al usuario por donde quieres", este ultimo metodo, a mi juicio, es mas seguro porque te obliga a verificar que realmente el usuario pudo acceder o no a tal o cual parte.

Cuando tu activas **'rbacSetupEnabled'=>true,** (ver tema siguiente) entonces causas que Cruge te mantenga informado de cuales operaciones, tareas o roles fallaron cuando se intento pasar por ellas(en otras palabras, cuales son requeridos), te lo ira informando al pie de la pagina.


##Modo de Programacion del RBAC

Para activarlo, en la configuracion de tu aplicacion debes considerar estos dos argumentos:

**'rbacSetupEnabled'=>true,**

		Permitira que las operaciones se vayan creando (valor true) a medida que vas probando el sistema, de lo
		contrario deberas crear las operaciones a mano. Las operaciones que se crearan automaticamente
		seran: 'controller_nombredetucontroller' y 'action_tucontroller_tuaction'.


**'allowUserAlways'=>true,**

		Permitira el paso (valor true) al usuario aunque este no tenga permiso. Cuando estás
		en produccion ponlo en 'false' lo que causara que el usuario reciba una excepcion.

Para conocer que operaciones se requieren para un usuario especifico debes poner en tu layout principal la siguiente linea:

	<?php
		echo Yii::app()->user->ui->displayErrorConsole();
	?>

	(no siempre se veran los mensajes de permisos requeridos, para ello usa el log, lee el item a continuacion)

##Tips de Programacion del RBAC

Cuando quieras programar el RBAC con cruge, usa dos navegadores: uno abierto con un usuario administrador, para que puedas ir activando las operaciones para un rol especifico a medida que sea necesario, y abre otro navegador con el usuario que tenga el rol que quieres programar. No olvides tener activado el flag: rbacSetupEnabled.

Por ejemplo, quieres que el usuario 'juan' que tiene asignado el rol 'empleado_regular' tenga solo acceso
solo a donde quieres, entonces en el segundo navegador inicia sesion con 'juan', y tratas de ir al menu u operacion requerida, cruge ira informando al pie de la pagina los permisos requieridos. Luego con el navegador que tiene abierto el usuario 'admin' entonces vas verificando los permisos y se los asignas al rol.

Considera que cuando rbacSetupEnabled esta habilitado, entonces, asumiendo ademas que no hay ninguna operacion creada iras viendo que cruge creara las operaciones automaticamente de acuerdo a donde el usuario 'juan' vaya pasando, solo las crea si previamente no existen.

Ejemplo: no hay ninguna operacion creada, entonces el usuario juan quiere entrar a 'site/index', por tanto,
si la controladora 'siteController' esta manejada con el filtro: 'CrugeAccessControlFilter' (ver tema mas arriba) entonces veras que se creara una operacion llamada 'site_controller' y otra 'action_site_index', deberas entonces asignarle estas dos operaciones al rol al cual 'juan' pertenece.

##Usando el LOG

Adicionalmente todos los errores de permiso que se generen seran reportados en el log bajo el key 'rbac', para poder visualizar los errores en protected/runtime/application.log deberas configurar tu config/main.php para indicar el key del log:

	<?php
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, info, rbac',	// <--- agregar 'rbac'
				),
				// uncomment the following to show log messages on web pages
				//array('class'=>'CWebLogRoute'),
			),
		),
	?>

eso causara que en:

		protected/runtime/application.log

se emitan mensajes como estos:

		2012/07/27 15:24:25 [rbac] [application] PERMISO REQUERIDO:
		invitado
		iduser=2
		tipo:operacion
		itemName:action_catalog_imageh

##El superUsuario

Por defecto, cruge considera a un superusuario, para proposito de administracion, depuracion etc.
Para que cruge considere a un usuario como un "superusuario" entonces este debera tener como username el mismo valor configurado en CrugeModule::superuserName. Por defecto este valor viene configurado como 'admin'.

Para cambiar este valor, al igual que otros parametros de Cruge, no debes cambiar nada en CrugeModule,
sino mediante config asi:

		'cruge'=>array(
			...
			'superuserName'=>'administrador', (suponiendo que no te gusta 'admin')
			...
		),

Como trata Cruge al superAdministrador ?

Basica y unicamente en: cruge\components\CrugeWebUser.php, en el metodo: checkAccess, si es un superadmin, entonces siempre le dara permiso.

Como saber si estamos ante un superAdministrador ?

Consultando a:
	Yii::app()->user->getIsSuperAdmin()
mas corto:
	Yii::app()->user->isSuperAdmin

##El usuario Invitado

Cruge hace especial tratamiento al usuario invitado. Para esto CrugeModule contiene un atributo llamado **guestUserId** , el cual es usado para indicarle al sistema Cruge cual de sus usuarios existentes en la base de datos de usuarios es el invitado.

Por defecto cuando Cruge es instalado desde el script de base de datos (protected/modules/cruge/data), se crean dos usuarios:

	insert into `cruge_user`(username, email, password, state) values
	 ('admin', 'admin@tucorreo.com','admin',1)
	 ,('invitado', 'invitado','nopassword',1)
	;

Siendo 'admin' el usuario con ID 1, y siendo 'invitado' el usuario con ID 2. Por esto veras que por defecto en CrugeModule.php ya esta predefinido el atributo guestUserId en 2. No lo cambies a menos que cambies el ID del usuario invitado.

**Roles para el usuario Invitado**

Si no asignas al usuario invitado a ningun rol entonces no tendrá acceso a ninguna parte. Por tanto debes:

1. Crea un rol llamado 'invitado'.

2. Asignale a ese rol las operaciones necesarias, por ejemplo 'controller_site', 'action_site_index', 'action_site_contact', 'action_site_login' y otras que vayas viendo que se requieran (usa el LOG, ver tema anterior).

3. Asigna este rol creado al usuario invitado.

No necesariamente el rol del 'invitado' debe llamarse 'invitado'. Por conveniencia es sano que asi sea pero no es indispensable.

**Como trata el sistema de usuarios al inviatdo**

Por defecto en YII cuando tu llamas a **Yii::app()->user->id** esta devuelve 0 (cero) cuando un usuario es invitado.

En Cruge esta misma llamada a **Yii::app()->user->id** devolverá al valor de: CrugeModule::guestUserId, por defecto 2.

Puedes confiar en Yii::app()->user->isGuest.  ya que ésta considera todo esto para saber si el usuario es un invitado.

##Encriptando las claves

Por defecto Cruge trae dos usuarios, admin e invitado.  En el caso de admin la clave es 'admin', y por defecto además Cruge trae la encriptacion de claves desactivada, para facilitar el trabajo mientras se instala. Esto se modifica en: 'useEncryptedPassword' => false (del config/main).

Si quieres encriptar las claves, es decir, que en la tabla de usuarios estas no sean visibles, entonces deberas tomar un paso extra: encriptar tu clave de admin y guardarla via base de datos en la tabla de usuarios (cruge_user),
luego activas la encriptacion y con eso podrás acceder y veras que los nuevos usuarios que se vayan creando tendránsu nueva clave encriptada.

**Encriptación MD5. Donde esta ubicada y donde cambiarla**

Hay dos sitios en Cruge en donde se maneja la encriptacion:

* cruge\models\auth\CrugeAuthDefault.php
Aqui se valida la autenticacion del usuario que esta intentando acceder, se convierte la clave a su MD5 y se compara con la almacenada.

		private function _getPwd(){
			if(CrugeUtil::config()->useEncryptedPassword == true)
				return md5($this->password);
			return $this->password;
		}

* cruge\components\CrugeUserManager.php
Aqui se maneja el cambio de clave.

		public function changePassword(ICrugeStoredUser $user, $newPassword){
			$epwd = $newPassword;
			if(CrugeUtil::config()->useEncryptedPassword == true)
				$epwd = md5($newPassword);
			$user->password = $epwd;
		}

##Filtros

Cruge permite que se pueda extender mas alla usando filtros. Existen varios tipos de filtros, todos se instalan en config/main y disponen de una interfaz (interface) que debes respetar, a continuación la lista de filtros, si necesitas crear un filtro nuevo fijate en como esta hecho el filtro por defecto:

* **filtros de autenticacion:**
permite que amplies como se busca un usuario para ser autenticado.
protected\modules\cruge\models\auth\CrugeAuthDefault.php

* **filtros de sesion:**
permite que puedas controlar como se entrega una sesion a un usuario, inclusive puedes denegarla.
protected\modules\cruge\models\filters\DefaultSessionFilter.php

* **filtro de actualizacion:**
permite saber si un usuario actualizo su perfil.
protected\modules\cruge\models\filters\DefaultUserFilter.php

Debido a lo extenso de Cruge no he tenido tiempo de documentar bien estos filtros, pero es bastante intuitivo.


##Errores Frecuentes

"no se pudo hallar el sistema de configuracion, quiza la tabla cruge_system esta vacia o ha indicado un identificador de sistema inexistente."
este error es generado cuando se ha instalado cruge pero la tabla cruge_system no tienen ningun dato. por defecto cuando el script sql se instala por primera vez este trae datos para cruge_system.  Cruge_system es una fila que informa acerca
de la variables de un sistema, pueden crearse varias configuraciones a las cuales se les hace referencia por su nombre, si en config/main hay una referencia a esta configuracion y esta no existe en cruge_system entonces este error aparecera.


##Traduccion

Si ves, Cruge escribe los mensajes en español, mientras que su codigo esta en inglés. Todos los mensajes se dirigen a la clase CrugeTranslator::t("mensaje en español"), por tanto ese es el punto para traducir a otro idioma. En un futuro nuevo commit hare un nuevo filtro, para traducir, sin que tengas que tocar nada dentro de CrugeTranslator.

pronto se implementara la internacionalizacion.


##Diseño interno de Cruge - Diagramas UML

Es importante conocer como esta diseñado Cruge, para esto proveeré tres diagramas importantes, no son todos, hay mas, pero estos son los indispensables para conocer que sucede cuando se click en "Login":

En este primero diagrama se muestran las clases que estan involucradas (junto a su relacion con otras clases).

![diagrama de clases - login][2]

En el segundo diagrama puedes observar que sucede en lineas generales cuando se presiona el boton login.

![diagrama de actividad login][3]

Finalmente aqui hay una secuencia en el tiempo y una vista de las clases involucradas, este diagrama se lee de izquierda a derecha, arriba en los cuadros grandes al tope se listan las clases involucradas, de cada clase sale una linea vertical larga de la cual a su vez salen flechas hacia otras clases, esas flechas son acciones a realizarse, llamadas, etc.

![diagrama de secuencia login][4]

[1]: https://bitbucket.org/christiansalazarh/cruge/downloads/screenshots.gif
[2]: https://bitbucket.org/christiansalazarh/cruge/downloads/Diagrama-de-clases-proceso-de-autenticacion2.png
[3]: https://bitbucket.org/christiansalazarh/cruge/downloads/diagrama-de-actividad--autenticaion.png
[4]: https://bitbucket.org/christiansalazarh/cruge/downloads/diag.secuencia-de-login2.png
