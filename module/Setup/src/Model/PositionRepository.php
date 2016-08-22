<?php
namespace Setup\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;

class PositionRepository implements RepositoryInterface{
	private $tableGateway;

	public function __construct(AdapterInterface $adapter){
		$this->tableGateway = new TableGateway('HR_POSITIONS',$adapter);		
	}
	public function add(Model $model){
		//print_r($model->getArrayCopyForDb());die();
		$this->tableGateway->insert($model->getArrayCopyForDb());
	}
	public function edit(Model $model,$id,$modifiedDt){
		$array = $model->getArrayCopyForDB();
		$newArray = array_merge($array,["MODIFIED_DT"=>$modifiedDt]);
		$this->tableGateway->update($newArray,["POSITION_ID"=>$id]);
	}
	public function delete($id){
		$this->tableGateway->delete(["POSITION_ID"=>$id]);
	}
	public function fetchAll(){
		return $this->tableGateway->select();
	}
	public function fetchActiveRecord()
    {
         return  $rowset= $this->tableGateway->select(['STATUS'=>'E']);       
    }
	public function fetchById($id){
		$row = $this->tableGateway->select(["POSITION_ID"=>$id]);
		return $row->current();
	}
}