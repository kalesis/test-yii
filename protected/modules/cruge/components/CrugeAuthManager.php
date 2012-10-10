<?php
/**
	CrugeAuthManager

	este modulo requiere instalacion en config/main.php :
		'components'=>array(
			'authManager' => array(
				'class' => 'application.modules.cruge.components.CrugeAuthManager',
			),
		),
	
	para acceder a el:
		Yii::app()->authManager
	o
		Yii::app()->user->rbac

	para consultar si el usuario actual tiene permiso para alguna operacion:
		if(Yii::app()->user->checkAccess('createPost')){...}


	funciones extendidas:
	
		permiten listar items. no estan declaradas como parte de la interfaz original de Yii
	
		Yii::app()->user->rbac->roles;
		Yii::app()->user->rbac->tasks;
		Yii::app()->user->rbac->operations;
	
		
	ejemplo:
	
		$auth=Yii::app()->authManager;
		 
		$auth->createOperation('createPost','create a post');
		$auth->createOperation('readPost','read a post');
		$auth->createOperation('updatePost','update a post');
		$auth->createOperation('deletePost','delete a post');
		 
		$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
		$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
		$task->addChild('updatePost');
		 
		$role=$auth->createRole('reader');
		$role->addChild('readPost');
		 
		$role=$auth->createRole('author');
		$role->addChild('reader');
		$role->addChild('createPost');
		$role->addChild('updateOwnPost');
		 
		$role=$auth->createRole('editor');
		$role->addChild('reader');
		$role->addChild('updatePost');
		 
		$role=$auth->createRole('admin');
		$role->addChild('editor');
		$role->addChild('author');
		$role->addChild('deletePost');

		// se asignan los roles a los usuarios, aqui el iduser es el nombre, pero puede (y debe)
		// ser el Yii::app()->user->id (id=que invoca a user->getId())
		
		$auth->assign('reader','readerA');
		$auth->assign('author','authorB');
		$auth->assign('editor','editorC');
		$auth->assign('admin','adminD');	
	
	@author: Christian Salazar H. <christiansalazarh@gmail.com> @bluyell
	@copyright Copyright &copy; 2008-2012 Yii Software LLC
	@license http://www.yiiframework.com/license/

*/
class CrugeAuthManager extends CAuthManager implements IAuthManager {

	/**
	 * @var string the ID of the {@link CDbConnection} application component. Defaults to 'db'.
	 * The database must have the tables as declared in "framework/web/auth/*.sql".
	 */
	public $connectionID='db';
	/**
	 * @var CDbConnection the database connection. By default, this is initialized
	 * automatically as the application component whose ID is indicated as {@link connectionID}.
	 */
	public $db;


	
	
	public function init()
	{
		parent::init();
		$this->getDbConnection();// para inicializar db
	}
	
	/** retorna el nombre de una tabla configurandola para los prefijos definidos en el modulo
		$table: uno de {'authitem', 'authitemchild', 'authassignment'}
	*/
	protected function getTableName($table){
		return CrugeUtil::getTableName($table);
	}
	
	public function usingSqlite(){
		return false;
	}
	
	
	/**
	 * Performs access check for the specified user.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($itemName,$userId,$params=array())
	{
		$assignments=$this->getAuthAssignments($userId);
		return $this->checkAccessRecursive($itemName,$userId,$params,$assignments);
	}	

	/**
	 * Performs access check for the specified user.
	 * This method is internally called by {@link checkAccess}.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @param array $assignments the assignments to the specified user
	 * @return boolean whether the operations can be performed by the user.
	 * @since 1.1.3
	 */
	protected function checkAccessRecursive($itemName,$userId,$params,$assignments)
	{
		if(($item=$this->getAuthItem($itemName))===null)
			return false;
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CDbAuthManager');
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData()))
		{
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($assignments[$itemName]))
			{
				$assignment=$assignments[$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
			$parents=$this->db->createCommand()
				->select('parent')
				->from($this->getTableName('authitemchild'))
				->where('child=:name', array(':name'=>$itemName))
				->queryColumn();
			foreach($parents as $parent)
			{
				if($this->checkAccessRecursive($parent,$userId,$params,$assignments))
					return true;
			}
		}
		return false;
	}

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName,$childName)
	{
		if($itemName===$childName)
			throw new CException(Yii::t('yii','Cannot add "{name}" as a child of itself.',
					array('{name}'=>$itemName)));

		$rows=$this->db->createCommand()
			->select()
			->from($this->getTableName('authitem'))
			->where('name=:name1 OR name=:name2', array(
				':name1'=>$itemName,
				':name2'=>$childName
			))
			->queryAll();

		if(count($rows)==2)
		{
			if($rows[0]['name']===$itemName)
			{
				$parentType=$rows[0]['type'];
				$childType=$rows[1]['type'];
			}
			else
			{
				$childType=$rows[0]['type'];
				$parentType=$rows[1]['type'];
			}
			$this->checkItemChildType($parentType,$childType);
			if($this->detectLoop($itemName,$childName))
				throw new CrugeException(Yii::t('yii','Cannot add "{child}" as a child of "{name}". A loop has been detected.',
					array('{child}'=>$childName,'{name}'=>$itemName)));

			$this->db->createCommand()
				->insert($this->getTableName('authitemchild'), array(
					'parent'=>$itemName,
					'child'=>$childName,
				));
		}
		else
			throw new CrugeException(Yii::t('yii','Either "{parent}" or "{child}" does not exist.',array('{child}'=>$childName,'{parent}'=>$itemName)));
	}

	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName)
	{
		return $this->db->createCommand()
			->delete($this->getTableName('authitemchild'), 'parent=:parent AND child=:child', array(
				':parent'=>$itemName,
				':child'=>$childName
			)) > 0;
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName)
	{
		return $this->db->createCommand()
			->select('parent')
			->from($this->getTableName('authitemchild'))
			->where('parent=:parent AND child=:child', array(
				':parent'=>$itemName,
				':child'=>$childName))
			->queryScalar() !== false;
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names (available since version 1.0.5).
	 * @return array all child items of the parent
	 */
	public function getItemChildren($names)
	{
		if(is_string($names))
			$condition='parent='.$this->db->quoteValue($names);
		else if(is_array($names) && $names!==array())
		{
			foreach($names as &$name)
				$name=$this->db->quoteValue($name);
			$condition='parent IN ('.implode(', ',$names).')';
		}

		$rows=$this->db->createCommand()
			->select('name, type, description, bizrule, data')
			->from(array(
				$this->getTableName('authitem'),
				$this->getTableName('authitemchild')
			))
			->where($condition.' AND name=child')
			->queryAll();

		$children=array();
		foreach($rows as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$children[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $children;
	}

	/**
	 * Assigns an authorization item to a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CrugeException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($itemName,$userId,$bizRule=null,$data=null)
	{
		/*
		if($this->usingSqlite() && $this->getAuthItem($itemName)===null)
			throw new CrugeException(Yii::t('yii','The item "{name}" does not exist.',array('{name}'=>$itemName)));
		*/
		
		// por christian salazar
		if($userId == '' || $userId==null)
			return null;

		$this->db->createCommand()
			->insert($this->getTableName('authassignment'), array(
				'itemname'=>$itemName,
				'userid'=>$userId,
				'bizrule'=>$bizRule,
				'data'=>serialize($data)
			));
		return new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	public function revoke($itemName,$userId)
	{
		return $this->db->createCommand()
			->delete($this->getTableName('authassignment'), 'itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId
			)) > 0;
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($itemName,$userId)
	{
		return $this->db->createCommand()
			->select('itemname')
			->from($this->getTableName('authassignment'))
			->where('itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryScalar() !== false;
	}

	/**
	 * Returns the item assignment information.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAuthAssignment($itemName,$userId)
	{
		$row=$this->db->createCommand()
			->select()
			->from($this->getTableName('authassignment'))
			->where('itemname=:itemname AND userid=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryRow();
		if($row!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	
	/** 
	 * Retorna un array con los userid que tienen el item asignado
	 * @param string $itemName el item a buscar
	 * @return array con los $userid
	 */
	public function getUsersAssigned($itemName)
	{
		$rows=$this->db->createCommand()
			->select()
			->from($this->getTableName('authassignment'))
			->where('itemname=:itemname ', array(
				':itemname'=>$itemName,
				))
			//->group('userid')
			->queryAll();
		$users=array();
		if($rows != null)
		foreach($rows as $row)
			if(!in_array($row['userid'],$users))
				$users[]=$row['userid'];
		return $users;
	}


	/** 
	 * Retorna un array con todos los "parents" de un item hallados en authitemchild
	 *
	 * este metodo permite ir hacia atras, lo opuesto a getChildrens, permitiendo conocer
	 * quienes hacen referencia a un authItem
	 *  
	 * @param string $itemName el item a buscar
	 * @return array con los CAuthItems que hacen la referencia al item.
	 */
	public function getParents($itemName)
	{
		$rows=$this->db->createCommand()
			->select()
			->from($this->getTableName('authitemchild'))
			->where('child=:itemname ', array(
					':itemname'=>$itemName,
				))
			->queryAll();
		$parents=array();
		if($rows != null)
			foreach($rows as $row)
				$parents[] = $this->getAuthItem($row['parent']);
		return $parents;
	}

	
	/** 
	 * Retorna el numero de userid's que tienen el item asignado
	 * @param string $itemName el item a buscar
	 * @return cantidad de usuarios asignados
	 */
	public function getCountUsersAssigned($itemName)
	{
		// TODO: optimizar esto con una consulta de agrupacion y cuenta
		//
		$ar = $this->getUsersAssigned($itemName);
		return count($ar);
	}
	
	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAuthAssignments($userId)
	{
		$rows=$this->db->createCommand()
			->select()
			->from($this->getTableName('authassignment'))
			->where('userid=:userid', array(':userid'=>$userId))
			->queryAll();
		$assignments=array();
		foreach($rows as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
			
		}
		return $assignments;
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment)
	{
		$this->db->createCommand()
			->update($this->getTableName('authassignment'), array(
				'bizrule'=>$assignment->getBizRule(),
				'data'=>serialize($assignment->getData()),
			), 'itemname=:itemname AND userid=:userid', array(
				'itemname'=>$assignment->getItemName(),
				'userid'=>$assignment->getUserId()
			));
	}

	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	public function getAuthItems($type=null,$userId=null)
	{
		if($type===null && $userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->getTableName('authitem'));
		}
		else if($userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->getTableName('authitem'))
				->where('type=:type', array(':type'=>$type));
		}
		else if($type===null)
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->getTableName('authitem').' t1',
					$this->getTableName('authassignment').' t2'
				))
				->where('name=itemname AND userid=:userid', array(':userid'=>$userId));
		}
		else
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->getTableName('authitem').' t1',
					$this->getTableName('authassignment').' t2'
				))
				->where('name=itemname AND type=:type AND userid=:userid', array(
					':type'=>$type,
					':userid'=>$userId
				));
		}
		$items=array();
		foreach($command->queryAll() as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$items[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $items;
	}

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CrugeException if an item with the same name already exists
	 */
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null)
	{
		$this->db->createCommand()
			->insert($this->getTableName('authitem'), array(
				'name'=>$name,
				'type'=>$type,
				'description'=>$description,
				'bizrule'=>$bizRule,
				'data'=>serialize($data)
			));
		return new CAuthItem($this,$name,$type,$description,$bizRule,$data);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeAuthItem($name)
	{
		if($this->usingSqlite())
		{
			$this->db->createCommand()
				->delete($this->getTableName('authitemchild'), 'parent=:name1 OR child=:name2', array(
					':name1'=>$name,
					':name2'=>$name
			));
			$this->db->createCommand()
				->delete($this->getTableName('authassignment'), 'itemname=:name', array(
					':name'=>$name,
			));
		}

		return $this->db->createCommand()
			->delete($this->getTableName('authitem'), 'name=:name', array(
				':name'=>$name
			)) > 0;
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	public function getAuthItem($name)
	{
		$row=$this->db->createCommand()
			->select()
			->from($this->getTableName('authitem'))
			->where('name=:name', array(':name'=>$name))
			->queryRow();

		if($row!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveAuthItem($item,$oldName=null)
	{
		if($this->usingSqlite() && $oldName!==null && $item->getName()!==$oldName)
		{
			$this->db->createCommand()
				->update($this->getTableName('authitemchild'), array(
					'parent'=>$item->getName(),
				), 'parent=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->getTableName('authitemchild'), array(
					'child'=>$item->getName(),
				), 'child=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->getTableName('authassignment'), array(
					'itemname'=>$item->getName(),
				), 'itemname=:whereName', array(
					':whereName'=>$oldName,
				));
		}

		$this->db->createCommand()
			->update($this->getTableName('authitem'), array(
				'name'=>$item->getName(),
				'type'=>$item->getType(),
				'description'=>$item->getDescription(),
				'bizrule'=>$item->getBizRule(),
				'data'=>serialize($item->getData()),
			), 'name=:whereName', array(
				':whereName'=>$oldName===null?$item->getName():$oldName,
			));
	}

	/**
	 * Saves the authorization data to persistent storage.
	 */
	public function save()
	{
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAuthAssignments();
		$this->db->createCommand()->delete($this->getTableName('authitemchild'));
		$this->db->createCommand()->delete($this->getTableName('authitem'));
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments()
	{
		$this->db->createCommand()->delete($this->getTableName('authassignment'));
	}

	/**
	 * Checks whether there is a loop in the authorization item hierarchy.
	 * @param string $itemName parent item name
	 * @param string $childName the name of the child item that is to be added to the hierarchy
	 * @return boolean whether a loop exists
	 */
	public function detectLoop($itemName,$childName)
	{
		if($childName===$itemName)
			return true;
		foreach($this->getItemChildren($childName) as $child)
		{
			if($this->detectLoop($itemName,$child->getName()))
				return true;
		}
		return false;
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CrugeException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if($this->db!==null)
			return $this->db;
		else if(($this->db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
			return $this->db;
		else
			throw new CrugeException(Yii::t('yii','CDbAuthManager.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
				array('{id}'=>$this->connectionID)));
	}

	
	/* extension:  no pertenece a la interfaz
	
	*/
	public function getAuthItemTypeName($type,$booleanPlural=false){
		if($type == CAuthItem::TYPE_ROLE)
			return $booleanPlural==false ? "rol" : "roles";
		if($type == CAuthItem::TYPE_TASK)
			return $booleanPlural==false ? "tarea" : "tareas";
		if($type == CAuthItem::TYPE_OPERATION)
			return $booleanPlural==false ? "operacion" : "operaciones";
		return $type;
	}
	
	public function nextType(CAuthItem $item){
		if($item->type == CAuthItem::TYPE_ROLE)
			return CAuthItem::TYPE_TASK;
		if($item->type == CAuthItem::TYPE_TASK)
			return CAuthItem::TYPE_OPERATION;
		return null;
	}
	
	public function getRoles($userId = NULL){
		return $this->getAuthItems(CAuthItem::TYPE_ROLE);
	}
	public function getTasks($userId = NULL){
		return $this->getAuthItems(CAuthItem::TYPE_TASK);
	}
	public function getOperations($userId = NULL){
		return $this->getAuthItems(CAuthItem::TYPE_OPERATION);
	}
	public function getDataProviderRoles($pageSize=20){
		return new CArrayDataProvider($this->getRoles(), array(
			'keyField'=>'name',
			'sort'=>array(
				'defaultOrder'=>array('name'),
			),
			'pagination'=>array(
				'pageSize'=>$pageSize,
			),
		));		
	}
	public function getDataProviderTasks($pageSize=20){
		return new CArrayDataProvider($this->getTasks(), array(
			'keyField'=>'name',
			'sort'=>array(
				'defaultOrder'=>array('name'),
			),
			'pagination'=>array(
				'pageSize'=>$pageSize,
			),
		));				
	}
	public function getDataProviderOperations($pageSize=20){
		return new CArrayDataProvider($this->getOperations(), array(
			'keyField'=>'name',
			'sort'=>array(
				'defaultOrder'=>array('name'),
			),
			'pagination'=>array(
				'pageSize'=>$pageSize,
			),
		));				
	}	
	public function getRolesAsOptions($emptyLabel=null){
		$ar = array();
		if($emptyLabel != null)
			$ar['']=$emptyLabel;
		
		foreach($this->roles as $rol){
			$ar[$rol->name] = $rol->name;
		}
		return $ar;
	}
	
}// finclase