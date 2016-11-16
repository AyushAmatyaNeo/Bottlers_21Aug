<?php

namespace Setup\Repository;

use Application\Helper\Helper;
use Application\Model\Model;
use Application\Repository\RepositoryInterface;
use Setup\Model\Branch;
use Setup\Model\EmployeeFile;
use Setup\Model\HrEmployees;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

class EmployeeRepository implements RepositoryInterface {

    private $gateway;
    private $adapter;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->gateway = new TableGateway('HR_EMPLOYEES', $adapter);
    }

    public function fetchAll() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from("HR_EMPLOYEES");
        $select->columns(Helper::convertColumnDateFormat($this->adapter, new HrEmployees(), ['birthDate']), false);
        $select->where(['STATUS' => 'E']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $tempArray = [];
        foreach ($result as $item) {
            $tempObject = new HrEmployees();
            $tempObject->exchangeArrayFromDB($item);
            array_push($tempArray, $tempObject);
        }
        return $tempArray;
    }

    public function fetchById($id) {
        $rowset = $this->gateway->select(function (Select $select) use ($id) {
            $select->columns(Helper::convertColumnDateFormat($this->adapter, new HrEmployees(), [
                        'birthDate',
                        'famSpouseBirthDate',
                        'famSpouseWeddingAnniversary',
                        'idDrivingLicenseExpiry',
                        'idCitizenshipIssueDate',
                        'idPassportExpiry',
                        'joinDate'
                    ]), false);

            $select->where(['EMPLOYEE_ID' => $id]);
        });
        return $rowset->current();
    }

    public function fetchForProfileById($id) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['E' => HrEmployees::TABLE_NAME]);
        $select->columns([
            Helper::dateExpression(HrEmployees::BIRTH_DATE, "E"),
            Helper::columnExpression(HrEmployees::FIRST_NAME, "E"),
            Helper::columnExpression(HrEmployees::MIDDLE_NAME, "E"),
            Helper::columnExpression(HrEmployees::LAST_NAME, "E"),
            Helper::columnExpression(HrEmployees::GENDER_ID, "E"),
            Helper::columnExpression(HrEmployees::MOBILE_NO, "E"),
                ], true);
        $select->join(['F' => EmployeeFile::TABLE_NAME], "E." . HrEmployees::EMPLOYEE_ID . "=F." . EmployeeFile::EMPLOYEE_ID);
        $select->where(["E." . HrEmployees::EMPLOYEE_ID . "=$id"]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }

    public function add(Model $model) {
        $this->gateway->insert($model->getArrayCopyForDB());
    }

    public function delete($id) {
//        $this->gateway->update(['STATUS'=>'D','MODIFIED_DT'=>Helper::getcurrentExpressionDate()],['EMPLOYEE_ID' => $id]);
        $this->gateway->update(['STATUS' => 'D'], ['EMPLOYEE_ID' => $id]);
    }

    public function edit(Model $model, $id) {
        $tempArray = $model->getArrayCopyForDB();

        if (array_key_exists('CREATED_DT', $tempArray)) {
            unset($tempArray['CREATED_DT']);
        }
        if (array_key_exists('EMPLOYEE_ID', $tempArray)) {
            unset($tempArray['EMPLOYEE_ID']);
        }
        if (array_key_exists('STATUS', $tempArray)) {
            unset($tempArray['STATUS']);
        }
        $this->gateway->update($tempArray, ['EMPLOYEE_ID' => $id]);
    }

    public function branchEmpCount() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();

        $select->columns([Helper::columnExpression(HrEmployees::EMPLOYEE_ID, 'E', "COUNT"), HrEmployees::BRANCH_ID], true);
        $select->from(['E' => HrEmployees::TABLE_NAME]);
//        $select->join(["B" => Branch::TABLE_NAME], "E." . HrEmployees::BRANCH_ID . " = B." . Branch::BRANCH_ID,[Branch::BRANCH_ID, Branch::BRANCH_NAME]);
        $select->group(["E." . HrEmployees::BRANCH_ID]);

        $statement = $sql->prepareStatementForSqlObject($select);
//        print_r($statement->getSql());
//        exit;
        return $statement->execute();
    }

}
