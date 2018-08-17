<?php
namespace Setup\Repository;

use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Application\Model\Model;
use Application\Repository\HrisRepository;
use Application\Repository\RepositoryInterface;
use AttendanceManagement\Model\AttendanceDetail;
use AttendanceManagement\Model\ShiftAssign;
use AttendanceManagement\Model\ShiftSetup;
use Setup\Model\Branch;
use Setup\Model\Company;
use Setup\Model\Department;
use Setup\Model\Designation;
use Setup\Model\Gender;
use Setup\Model\HrEmployees;
use Setup\Model\Position;
use Setup\Model\ServiceEventType;
use Setup\Model\ServiceType;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

class EmployeeRepository extends HrisRepository implements RepositoryInterface {

    private $vdcGateway;
    private $districtGateway;
    private $zoneGateway;

    public function __construct(AdapterInterface $adapter, $tableName = null) {
        parent::__construct($adapter, HrEmployees::TABLE_NAME);
        $this->vdcGateway = new TableGateway('HRIS_VDC_MUNICIPALITIES', $adapter);
        $this->districtGateway = new TableGateway('HRIS_DISTRICTS', $adapter);
        $this->zoneGateway = new TableGateway('HRIS_ZONES', $adapter);
    }

    public function fetchAll() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from("HRIS_EMPLOYEES");
        $colList = EntityHelper::getColumnNameArrayWithOracleFns(HrEmployees::class, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [HrEmployees::BIRTH_DATE]);

        $select->columns($colList, false);
        $select->where(['STATUS' => 'E', 'RETIRED_FLAG' => 'N', "JOIN_DATE <= SYSDATE"]);
        $select->order(['UPPER(FIRST_NAME)', 'UPPER(MIDDLE_NAME)', 'UPPER(LAST_NAME)']);

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

    public function fetchSubstituteEmployees($employeeId = null, $excludeSelf = false, $excludeRecAndApp = false) {
        $condition = " WHERE 1=1 ";
        if ($employeeId != null && $excludeSelf && !$excludeRecAndApp) {
            $condition = " WHERE EMPLOYEE != {$employeeId} ";
        } else
        if ($employeeId != null && $excludeSelf && $excludeRecAndApp) {
            $condition = ",( SELECT EMPLOYEE_ID,RECOMMEND_BY,APPROVED_BY FROM HRIS_RECOMMENDER_APPROVER WHERE EMPLOYEE_ID = {$employeeId} ) RA WHERE E.EMPLOYEE != RA.EMPLOYEE_ID AND E.EMPLOYEE != RA.RECOMMEND_BY AND E.EMPLOYEE != RA.APPROVED_ID";
        }

        $sql = "
                SELECT EMPLOYEE_ID,
                  FULL_NAME
                FROM HRIS_EMPLOYEES E
                {$condition} 
                AND E.STATUS    ='E'
                AND E.RETIRED_FLAG='N'
                AND E.IS_ADMIN    ='N'";
        print $sql;
        exit;
    }

    public function fetchAllForAttendance() {
        $sql = "SELECT E.* FROM HRIS_EMPLOYEES E
        JOIN HRIS_EMPLOYEE_SHIFT_ASSIGN ESA ON (E.EMPLOYEE_ID=ESA.EMPLOYEE_ID) JOIN HRIS_SHIFTS S ON (ESA.SHIFT_ID=S.SHIFT_ID) 
        WHERE  
        (CASE
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'SUN' THEN ( CASE WHEN (S.WEEKDAY1 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'MON' THEN ( CASE WHEN (S.WEEKDAY2 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'TUE' THEN ( CASE WHEN (S.WEEKDAY3 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'WED' THEN ( CASE WHEN (S.WEEKDAY4 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'THU' THEN ( CASE WHEN (S.WEEKDAY5 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'FRI' THEN ( CASE WHEN (S.WEEKDAY6 = 'DAY_OFF') THEN 0 ELSE 1 END )
        WHEN  trim(TO_CHAR(SYSDATE, 'DY')) = 'SAT' THEN ( CASE WHEN (S.WEEKDAY7 = 'DAY_OFF') THEN 0 ELSE 1 END )
        END)=1 AND E.STATUS='E' AND E.RETIRED_FLAG='N' AND E.JOIN_DATE <= SYSDATE AND S.STATUS ='E' AND ESA.STATUS='E'
        ";
        $statement = $this->adapter->query($sql);
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
        $rowset = $this->tableGateway->select(function (Select $select) use ($id) {
            $select->columns(EntityHelper::getColumnNameArrayWithOracleFns(HrEmployees::class, [HrEmployees::FULL_NAME, HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [
                    HrEmployees::BIRTH_DATE,
                    HrEmployees::FAM_SPOUSE_BIRTH_DATE,
                    HrEmployees::FAM_SPOUSE_WEDDING_ANNIVERSARY,
                    HrEmployees::ID_DRIVING_LICENCE_EXPIRY,
                    HrEmployees::ID_CITIZENSHIP_ISSUE_DATE,
                    HrEmployees::ID_PASSPORT_EXPIRY,
                    HrEmployees::JOIN_DATE
                ]), false);
            $select->where(['EMPLOYEE_ID' => $id]);
        });
        return $rowset->current();
    }

    public function getById($id) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $E = 'E';
        $columns = EntityHelper::getColumnNameArrayWithOracleFns(HrEmployees::class, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME, HrEmployees::FULL_NAME], [
                HrEmployees::BIRTH_DATE,
                HrEmployees::FAM_SPOUSE_BIRTH_DATE,
                HrEmployees::FAM_SPOUSE_WEDDING_ANNIVERSARY,
                HrEmployees::ID_DRIVING_LICENCE_EXPIRY,
                HrEmployees::ID_CITIZENSHIP_ISSUE_DATE,
                HrEmployees::ID_PASSPORT_EXPIRY,
                HrEmployees::JOIN_DATE
                ], NULL, NULL, NULL, $E);
        $select->columns($columns, false);

        $select->from(['E' => HrEmployees::TABLE_NAME]);
        $select
            ->join(['B' => Branch::TABLE_NAME], "E." . HrEmployees::BRANCH_ID . "=B." . Branch::BRANCH_ID, ['BRANCH_NAME' => new Expression('INITCAP(B.BRANCH_NAME)')], 'left')
            ->join(['C' => Company::TABLE_NAME], "E." . HrEmployees::COMPANY_ID . "=C." . Company::COMPANY_ID, ['COMPANY_NAME' => new Expression('INITCAP(C.COMPANY_NAME)')], 'left')
            ->join(['G' => Gender::TABLE_NAME], "E." . HrEmployees::GENDER_ID . "=G." . Gender::GENDER_ID, ['GENDER_NAME' => new Expression('INITCAP(G.GENDER_NAME)')], 'left')
            ->join(['BG' => "HRIS_BLOOD_GROUPS"], "E." . HrEmployees::BLOOD_GROUP_ID . "=BG.BLOOD_GROUP_ID", ['BLOOD_GROUP_CODE'], 'left')
            ->join(['RG' => "HRIS_RELIGIONS"], "E." . HrEmployees::RELIGION_ID . "=RG.RELIGION_ID", ['RELIGION_NAME' => new Expression('INITCAP(RG.RELIGION_NAME)')], 'left')
            ->join(['CN' => "HRIS_COUNTRIES"], "E." . HrEmployees::COUNTRY_ID . "=CN.COUNTRY_ID", ['COUNTRY_NAME' => new Expression('INITCAP(CN.COUNTRY_NAME)')], 'left')
            ->join(['VM' => "HRIS_VDC_MUNICIPALITIES"], "E." . HrEmployees::ADDR_PERM_VDC_MUNICIPALITY_ID . "=VM.VDC_MUNICIPALITY_ID", ['VDC_MUNICIPALITY_NAME' => new Expression('INITCAP(VM.VDC_MUNICIPALITY_NAME)')], 'left')
            ->join(['VM1' => "HRIS_VDC_MUNICIPALITIES"], "E." . HrEmployees::ADDR_TEMP_VDC_MUNICIPALITY_ID . "=VM1.VDC_MUNICIPALITY_ID", ['VDC_MUNICIPALITY_NAME_TEMP' => 'VDC_MUNICIPALITY_NAME'], 'left')
            ->join(['D1' => Department::TABLE_NAME], "E." . HrEmployees::DEPARTMENT_ID . "=D1." . Department::DEPARTMENT_ID, ['DEPARTMENT_NAME' => new Expression('INITCAP(D1.DEPARTMENT_NAME)')], 'left')
            ->join(['DES1' => Designation::TABLE_NAME], "E." . HrEmployees::DESIGNATION_ID . "=DES1." . Designation::DESIGNATION_ID, ['DESIGNATION_TITLE' => new Expression('INITCAP(DES1.DESIGNATION_TITLE)')], 'left')
            ->join(['P1' => Position::TABLE_NAME], "E." . HrEmployees::POSITION_ID . "=P1." . Position::POSITION_ID, ['POSITION_NAME' => new Expression('INITCAP(P1.POSITION_NAME)'), "LEVEL_NO" => "P1.LEVEL_NO"], 'left')
            ->join(['S1' => ServiceType::TABLE_NAME], "E." . HrEmployees::SERVICE_TYPE_ID . "=S1." . ServiceType::SERVICE_TYPE_ID, ['SERVICE_TYPE_NAME' => new Expression('INITCAP(S1.SERVICE_TYPE_NAME)')], 'left')
            ->join(['SE1' => ServiceEventType::TABLE_NAME], "E." . HrEmployees::SERVICE_EVENT_TYPE_ID . "=SE1." . ServiceEventType::SERVICE_EVENT_TYPE_ID, ['SERVICE_EVENT_TYPE_NAME' => new Expression('INITCAP(SE1.SERVICE_EVENT_TYPE_NAME)')], 'left');
        $select->where(["E." . HrEmployees::EMPLOYEE_ID . "=$id"]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function fetchForProfileById($employeeId, $date = null) {
        $dateOn = ($date == null) ? 'TRUNC(SYSDATE)' : $date;
        $sql = "SELECT EH.*,
                  E.EMPLOYEE_CODE                                                   AS EMPLOYEE_CODE,
                  INITCAP(E.FIRST_NAME)                                             AS FIRST_NAME,
                  INITCAP(E.MIDDLE_NAME)                                            AS MIDDLE_NAME,
                  INITCAP(E.LAST_NAME)                                              AS LAST_NAME,
                  E.FULL_NAME                                                       AS FULL_NAME,
                  E.NAME_NEPALI                                                     AS NAME_NEPALI,
                  E.GENDER_ID                                                       AS GENDER_ID,
                  INITCAP(TO_CHAR(E.BIRTH_DATE, 'DD-MON-YYYY'))                     AS BIRTH_DATE,
                  E.BLOOD_GROUP_ID                                                  AS BLOOD_GROUP_ID,
                  E.RELIGION_ID                                                     AS RELIGION_ID,
                  E.SOCIAL_ACTIVITY                                                 AS SOCIAL_ACTIVITY,
                  E.TELEPHONE_NO                                                    AS TELEPHONE_NO,
                  E.MOBILE_NO                                                       AS MOBILE_NO,
                  E.EXTENSION_NO                                                    AS EXTENSION_NO,
                  E.EMAIL_OFFICIAL                                                  AS EMAIL_OFFICIAL,
                  E.EMAIL_PERSONAL                                                  AS EMAIL_PERSONAL,
                  E.SOCIAL_NETWORK                                                  AS SOCIAL_NETWORK,
                  E.EMRG_CONTACT_NAME                                               AS EMRG_CONTACT_NAME,
                  E.EMERG_CONTACT_NO                                                AS EMERG_CONTACT_NO,
                  E.EMERG_CONTACT_ADDRESS                                           AS EMERG_CONTACT_ADDRESS,
                  E.EMERG_CONTACT_RELATIONSHIP                                      AS EMERG_CONTACT_RELATIONSHIP,
                  E.ADDR_PERM_HOUSE_NO                                              AS ADDR_PERM_HOUSE_NO,
                  E.ADDR_PERM_WARD_NO                                               AS ADDR_PERM_WARD_NO,
                  E.ADDR_PERM_STREET_ADDRESS                                        AS ADDR_PERM_STREET_ADDRESS,
                  E.ADDR_PERM_COUNTRY_ID                                            AS ADDR_PERM_COUNTRY_ID,
                  E.ADDR_PERM_VDC_MUNICIPALITY_ID                                   AS ADDR_PERM_VDC_MUNICIPALITY_ID,
                  E.ADDR_TEMP_HOUSE_NO                                              AS ADDR_TEMP_HOUSE_NO,
                  E.ADDR_TEMP_WARD_NO                                               AS ADDR_TEMP_WARD_NO,
                  E.ADDR_TEMP_STREET_ADDRESS                                        AS ADDR_TEMP_STREET_ADDRESS,
                  E.ADDR_TEMP_COUNTRY_ID                                            AS ADDR_TEMP_COUNTRY_ID,
                  E.ADDR_TEMP_VDC_MUNICIPALITY_ID                                   AS ADDR_TEMP_VDC_MUNICIPALITY_ID,
                  E.FAM_FATHER_NAME                                                 AS FAM_FATHER_NAME,
                  E.FAM_FATHER_OCCUPATION                                           AS FAM_FATHER_OCCUPATION,
                  E.FAM_MOTHER_NAME                                                 AS FAM_MOTHER_NAME,
                  E.FAM_MOTHER_OCCUPATION                                           AS FAM_MOTHER_OCCUPATION,
                  E.FAM_GRAND_FATHER_NAME                                           AS FAM_GRAND_FATHER_NAME,
                  E.FAM_GRAND_MOTHER_NAME                                           AS FAM_GRAND_MOTHER_NAME,
                  E.MARITAL_STATUS                                                  AS MARITAL_STATUS,
                  E.FAM_SPOUSE_NAME                                                 AS FAM_SPOUSE_NAME,
                  E.FAM_SPOUSE_OCCUPATION                                           AS FAM_SPOUSE_OCCUPATION,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_BIRTH_DATE, 'DD-MON-YYYY'))          AS FAM_SPOUSE_BIRTH_DATE,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_WEDDING_ANNIVERSARY, 'DD-MON-YYYY')) AS FAM_SPOUSE_WEDDING_ANNIVERSARY,
                  E.ID_CARD_NO                                                      AS ID_CARD_NO,
                  E.ID_LBRF                                                         AS ID_LBRF,
                  E.ID_BAR_CODE                                                     AS ID_BAR_CODE,
                  E.ID_PROVIDENT_FUND_NO                                            AS ID_PROVIDENT_FUND_NO,
                  E.ID_DRIVING_LICENCE_NO                                           AS ID_DRIVING_LICENCE_NO,
                  E.ID_DRIVING_LICENCE_TYPE                                         AS ID_DRIVING_LICENCE_TYPE,
                  INITCAP(TO_CHAR(E.ID_DRIVING_LICENCE_EXPIRY, 'DD-MON-YYYY'))      AS ID_DRIVING_LICENCE_EXPIRY,
                  E.ID_THUMB_ID                                                     AS ID_THUMB_ID,
                  E.ID_PAN_NO                                                       AS ID_PAN_NO,
                  E.ID_ACCOUNT_NO                                                   AS ID_ACCOUNT_NO,
                  E.ID_RETIREMENT_NO                                                AS ID_RETIREMENT_NO,
                  E.ID_CITIZENSHIP_NO                                               AS ID_CITIZENSHIP_NO,
                  INITCAP(TO_CHAR(E.ID_CITIZENSHIP_ISSUE_DATE, 'DD-MON-YYYY'))      AS ID_CITIZENSHIP_ISSUE_DATE,
                  E.ID_CITIZENSHIP_ISSUE_PLACE                                      AS ID_CITIZENSHIP_ISSUE_PLACE,
                  E.ID_PASSPORT_NO                                                  AS ID_PASSPORT_NO,
                  INITCAP(TO_CHAR(E.ID_PASSPORT_EXPIRY, 'DD-MON-YYYY'))             AS ID_PASSPORT_EXPIRY,
                  INITCAP(TO_CHAR(E.JOIN_DATE, 'DD-MON-YYYY'))                      AS JOIN_DATE,
                  E.SALARY                                                          AS SALARY,
                  E.SALARY_PF                                                       AS SALARY_PF,
                  E.REMARKS                                                         AS REMARKS,
                  E.STATUS                                                          AS STATUS,
                  E.CREATED_DT                                                      AS CREATED_DT,
                  E.SERVICE_EVENT_TYPE_ID                                           AS SERVICE_EVENT_TYPE_ID,
                  E.COUNTRY_ID                                                      AS COUNTRY_ID,
                  E.PROFILE_PICTURE_ID                                              AS PROFILE_PICTURE_ID,
                  E.RETIRED_FLAG                                                    AS RETIRED_FLAG,
                  E.EMPLOYEE_TYPE                                                   AS EMPLOYEE_TYPE,
                  E.CREATED_BY                                                      AS CREATED_BY,
                  E.MODIFIED_BY                                                     AS MODIFIED_BY,
                  E.MODIFIED_DT                                                     AS MODIFIED_DT,
                  E.IS_HR                                                           AS IS_HR,
                  E.ADDR_TEMP_ZONE_ID                                               AS ADDR_TEMP_ZONE_ID,
                  E.ADDR_TEMP_DISTRICT_ID                                           AS ADDR_TEMP_DISTRICT_ID,
                  E.ADDR_PERM_ZONE_ID                                               AS ADDR_PERM_ZONE_ID,
                  E.ADDR_PERM_DISTRICT_ID                                           AS ADDR_PERM_DISTRICT_ID,
                  E.LOCATION_ID                                                     AS LOCATION_ID,
                  E.FUNCTIONAL_TYPE_ID                                              AS FUNCTIONAL_TYPE_ID,
                  E.FUNCTIONAL_LEVEL_ID                                             AS FUNCTIONAL_LEVEL_ID,
                  C.COMPANY_NAME                                                    AS COMPANY_NAME,
                  B1.BRANCH_NAME                                                    AS BRANCH,
                  D1.DEPARTMENT_NAME                                                AS DEPARTMENT,
                  DES1.DESIGNATION_TITLE                                            AS DESIGNATION,
                  P1.POSITION_NAME                                                  AS POSITION,
                  P1.LEVEL_NO                                                       AS LEVEL_NO,
                  S1.SERVICE_TYPE_NAME                                              AS SERVICE_TYPE,
                  SE1.SERVICE_EVENT_TYPE_NAME                                       AS SERVICE_EVENT_TYPE,
                  EF.FILE_PATH                                                      AS FILE_NAME,
                  RA.RECOMMEND_BY                                                   AS RECOMMENDER_ID,
                  RA.APPROVED_BY                                                    AS APPROVER_ID,
                  INITCAP(REC.FULL_NAME)                                            AS RECOMMENDER,
                  INITCAP(APP.FULL_NAME)                                            AS APPROVER,
                  F.FILE_PATH                                                       AS COMPANY_FILE_PATH,
                  F.FILE_CODE                                                       AS COMPANY_FILE_CODE,
                  F.FILE_NAME                                                       AS COMPANY_FILE_NAME
                FROM HRIS_EMPLOYEES E
                JOIN
                  (SELECT E.EMPLOYEE_ID,
                    (
                    CASE
                      WHEN H.TO_COMPANY_ID IS NOT NULL
                      THEN H.TO_COMPANY_ID
                      ELSE E.COMPANY_ID
                    END ) AS COMPANY_ID,
                    (
                    CASE
                      WHEN H.TO_BRANCH_ID IS NOT NULL
                      THEN H.TO_BRANCH_ID
                      ELSE E.BRANCH_ID
                    END ) AS BRANCH_ID,
                    (
                    CASE
                      WHEN H.TO_DEPARTMENT_ID IS NOT NULL
                      THEN H.TO_DEPARTMENT_ID
                      ELSE E.DEPARTMENT_ID
                    END ) AS DEPARTMENT_ID,
                    (
                    CASE
                      WHEN H.TO_DESIGNATION_ID IS NOT NULL
                      THEN H.TO_DESIGNATION_ID
                      ELSE E.DESIGNATION_ID
                    END ) AS DESIGNATION_ID,
                    (
                    CASE
                      WHEN H.TO_POSITION_ID IS NOT NULL
                      THEN H.TO_POSITION_ID
                      ELSE E.POSITION_ID
                    END ) AS POSITION_ID,
                    (
                    CASE
                      WHEN H.TO_SERVICE_TYPE_ID IS NOT NULL
                      THEN H.TO_SERVICE_TYPE_ID
                      ELSE E.SERVICE_TYPE_ID
                    END ) AS SERVICE_TYPE_ID,
                    (
                    CASE
                      WHEN H.TO_SALARY IS NOT NULL
                      THEN H.TO_SALARY
                      ELSE E.SALARY
                    END ) AS SALARY
                  FROM HRIS_EMPLOYEES E
                  LEFT JOIN HRIS_JOB_HISTORY H
                  ON (E.EMPLOYEE_ID     =H.EMPLOYEE_ID AND H.JOB_HISTORY_ID= HRIS_GET_SERVICE_STATUS(E.EMPLOYEE_ID,{$dateOn}))
                  ) EH ON (E.EMPLOYEE_ID=EH.EMPLOYEE_ID)
                LEFT JOIN HRIS_BRANCHES B1
                ON EH.BRANCH_ID=B1.BRANCH_ID
                LEFT JOIN HRIS_COMPANY C
                ON EH.COMPANY_ID=C.COMPANY_ID
                LEFT JOIN HRIS_EMPLOYEE_FILE F
                ON F.FILE_CODE=C.LOGO
                LEFT JOIN HRIS_DEPARTMENTS D1
                ON EH.DEPARTMENT_ID=D1.DEPARTMENT_ID
                LEFT JOIN HRIS_DESIGNATIONS DES1
                ON EH.DESIGNATION_ID=DES1.DESIGNATION_ID
                LEFT JOIN HRIS_POSITIONS P1
                ON EH.POSITION_ID=P1.POSITION_ID
                LEFT JOIN HRIS_SERVICE_TYPES S1
                ON EH.SERVICE_TYPE_ID=S1.SERVICE_TYPE_ID
                LEFT JOIN HRIS_SERVICE_EVENT_TYPES SE1
                ON E.SERVICE_EVENT_TYPE_ID=SE1.SERVICE_EVENT_TYPE_ID
                LEFT JOIN HRIS_EMPLOYEE_FILE EF
                ON E.PROFILE_PICTURE_ID=EF.FILE_CODE
                LEFT JOIN HRIS_RECOMMENDER_APPROVER RA
                ON E.EMPLOYEE_ID=RA.EMPLOYEE_ID
                LEFT JOIN HRIS_EMPLOYEES REC
                ON REC.EMPLOYEE_ID=RA.RECOMMEND_BY
                LEFT JOIN HRIS_EMPLOYEES APP
                ON APP.EMPLOYEE_ID  =RA.APPROVED_BY
                WHERE EH.EMPLOYEE_ID={$employeeId}";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        return $result->current();
    }

    public function add(Model $model) {
        $employeeData=$model->getArrayCopyForDB();
        // for hr with empower nbb bank start
        $employeeData['EMPLOYEE_STATUS']='Working';
        // for hr with empower nbb bank end
        $this->tableGateway->insert($employeeData);
    }

    public function delete($model) {
        $this->tableGateway->update(['STATUS' => 'D', 'DELETED_DATE' => $model->deletedDate, 'DELETED_BY' => $model->deletedBy], ['EMPLOYEE_ID' => $model->employeeId]);
    }

    public function edit(Model $model, $id) {
        $tempArray = $model->getArrayCopyForDB();
        $this->tableGateway->update($tempArray, ['EMPLOYEE_ID' => $id]);
    }

    public function branchEmpCount() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();

        $select->columns([Helper::columnExpression(HrEmployees::EMPLOYEE_ID, 'E', "COUNT"), HrEmployees::BRANCH_ID], true);
        $select->from(['E' => HrEmployees::TABLE_NAME]);
        $select->group(["E." . HrEmployees::BRANCH_ID]);
        $select->where(['E.STATUS' => 'E', 'E.RETIRED_FLAG' => 'N']);

        $statement = $sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function filterRecords($employeeId, $branchId, $departmentId, $designationId, $positionId, $serviceTypeId, $serviceEventTypeId, $getResult = null, $companyId = null, $employeeTypeId = null) {
        $condition = EntityHelper::getSearchConditon($companyId, $branchId, $departmentId, $positionId, $designationId, $serviceTypeId, $serviceEventTypeId, $employeeTypeId, $employeeId);
        $sql = "SELECT E.EMPLOYEE_ID                                                AS EMPLOYEE_ID,
              E.COMPANY_ID                                                      AS COMPANY_ID,
              E.EMPLOYEE_CODE                                                   AS EMPLOYEE_CODE,
              INITCAP(E.FIRST_NAME)                                             AS FIRST_NAME,
              INITCAP(E.MIDDLE_NAME)                                            AS MIDDLE_NAME,
              INITCAP(E.LAST_NAME)                                              AS LAST_NAME,
              E.NAME_NEPALI                                                     AS NAME_NEPALI,
              E.GENDER_ID                                                       AS GENDER_ID,
              INITCAP(TO_CHAR(E.BIRTH_DATE, 'DD-MON-YYYY'))                     AS BIRTH_DATE,
              E.BLOOD_GROUP_ID                                                  AS BLOOD_GROUP_ID,
              E.RELIGION_ID                                                     AS RELIGION_ID,
              E.SOCIAL_ACTIVITY                                                 AS SOCIAL_ACTIVITY,
              E.TELEPHONE_NO                                                    AS TELEPHONE_NO,
              E.MOBILE_NO                                                       AS MOBILE_NO,
              E.EXTENSION_NO                                                    AS EXTENSION_NO,
              E.EMAIL_OFFICIAL                                                  AS EMAIL_OFFICIAL,
              E.EMAIL_PERSONAL                                                  AS EMAIL_PERSONAL,
              E.SOCIAL_NETWORK                                                  AS SOCIAL_NETWORK,
              E.EMRG_CONTACT_NAME                                               AS EMRG_CONTACT_NAME,
              E.EMERG_CONTACT_NO                                                AS EMERG_CONTACT_NO,
              E.EMERG_CONTACT_ADDRESS                                           AS EMERG_CONTACT_ADDRESS,
              E.EMERG_CONTACT_RELATIONSHIP                                      AS EMERG_CONTACT_RELATIONSHIP,
              E.ADDR_PERM_HOUSE_NO                                              AS ADDR_PERM_HOUSE_NO,
              E.ADDR_PERM_WARD_NO                                               AS ADDR_PERM_WARD_NO,
              E.ADDR_PERM_STREET_ADDRESS                                        AS ADDR_PERM_STREET_ADDRESS,
              E.ADDR_PERM_COUNTRY_ID                                            AS ADDR_PERM_COUNTRY_ID,
              E.ADDR_PERM_VDC_MUNICIPALITY_ID                                   AS ADDR_PERM_VDC_MUNICIPALITY_ID,
              E.ADDR_TEMP_HOUSE_NO                                              AS ADDR_TEMP_HOUSE_NO,
              E.ADDR_TEMP_WARD_NO                                               AS ADDR_TEMP_WARD_NO,
              E.ADDR_TEMP_STREET_ADDRESS                                        AS ADDR_TEMP_STREET_ADDRESS,
              E.ADDR_TEMP_COUNTRY_ID                                            AS ADDR_TEMP_COUNTRY_ID,
              E.ADDR_TEMP_VDC_MUNICIPALITY_ID                                   AS ADDR_TEMP_VDC_MUNICIPALITY_ID,
              E.FAM_FATHER_NAME                                                 AS FAM_FATHER_NAME,
              E.FAM_FATHER_OCCUPATION                                           AS FAM_FATHER_OCCUPATION,
              E.FAM_MOTHER_NAME                                                 AS FAM_MOTHER_NAME,
              E.FAM_MOTHER_OCCUPATION                                           AS FAM_MOTHER_OCCUPATION,
              E.FAM_GRAND_FATHER_NAME                                           AS FAM_GRAND_FATHER_NAME,
              E.FAM_GRAND_MOTHER_NAME                                           AS FAM_GRAND_MOTHER_NAME,
              E.MARITAL_STATUS                                                  AS MARITAL_STATUS,
              E.FAM_SPOUSE_NAME                                                 AS FAM_SPOUSE_NAME,
              E.FAM_SPOUSE_OCCUPATION                                           AS FAM_SPOUSE_OCCUPATION,
              INITCAP(TO_CHAR(E.FAM_SPOUSE_BIRTH_DATE, 'DD-MON-YYYY'))          AS FAM_SPOUSE_BIRTH_DATE,
              INITCAP(TO_CHAR(E.FAM_SPOUSE_WEDDING_ANNIVERSARY, 'DD-MON-YYYY')) AS FAM_SPOUSE_WEDDING_ANNIVERSARY,
              E.ID_CARD_NO                                                      AS ID_CARD_NO,
              E.ID_LBRF                                                         AS ID_LBRF,
              E.ID_BAR_CODE                                                     AS ID_BAR_CODE,
              E.ID_PROVIDENT_FUND_NO                                            AS ID_PROVIDENT_FUND_NO,
              E.ID_DRIVING_LICENCE_NO                                           AS ID_DRIVING_LICENCE_NO,
              E.ID_DRIVING_LICENCE_TYPE                                         AS ID_DRIVING_LICENCE_TYPE,
              INITCAP(TO_CHAR(E.ID_DRIVING_LICENCE_EXPIRY, 'DD-MON-YYYY'))      AS ID_DRIVING_LICENCE_EXPIRY,
              E.ID_THUMB_ID                                                     AS ID_THUMB_ID,
              E.ID_PAN_NO                                                       AS ID_PAN_NO,
              E.ID_ACCOUNT_NO                                                   AS ID_ACCOUNT_NO,
              E.ID_RETIREMENT_NO                                                AS ID_RETIREMENT_NO,
              E.ID_CITIZENSHIP_NO                                               AS ID_CITIZENSHIP_NO,
              INITCAP(TO_CHAR(E.ID_CITIZENSHIP_ISSUE_DATE, 'DD-MON-YYYY'))      AS ID_CITIZENSHIP_ISSUE_DATE,
              E.ID_CITIZENSHIP_ISSUE_PLACE                                      AS ID_CITIZENSHIP_ISSUE_PLACE,
              E.ID_PASSPORT_NO                                                  AS ID_PASSPORT_NO,
              INITCAP(TO_CHAR(E.ID_PASSPORT_EXPIRY, 'DD-MON-YYYY'))             AS ID_PASSPORT_EXPIRY,
              INITCAP(TO_CHAR(E.JOIN_DATE, 'DD-MON-YYYY'))                      AS JOIN_DATE,
              E.SALARY                                                          AS SALARY,
              E.SALARY_PF                                                       AS SALARY_PF,
              E.REMARKS                                                         AS REMARKS,
              E.STATUS                                                          AS STATUS,
              E.CREATED_DT                                                      AS CREATED_DT,
              E.SERVICE_EVENT_TYPE_ID                                           AS SERVICE_EVENT_TYPE_ID,
              E.SERVICE_TYPE_ID                                                 AS SERVICE_TYPE_ID,
              E.POSITION_ID                                                     AS POSITION_ID,
              E.DESIGNATION_ID                                                  AS DESIGNATION_ID,
              E.DEPARTMENT_ID                                                   AS DEPARTMENT_ID,
              E.BRANCH_ID                                                       AS BRANCH_ID,
              E.APP_SERVICE_EVENT_TYPE_ID                                       AS APP_SERVICE_EVENT_TYPE_ID,
              E.APP_SERVICE_TYPE_ID                                             AS APP_SERVICE_TYPE_ID,
              E.APP_POSITION_ID                                                 AS APP_POSITION_ID,
              E.APP_DESIGNATION_ID                                              AS APP_DESIGNATION_ID,
              E.APP_DEPARTMENT_ID                                               AS APP_DEPARTMENT_ID,
              E.APP_BRANCH_ID                                                   AS APP_BRANCH_ID,
              E.COUNTRY_ID                                                      AS COUNTRY_ID,
              E.PROFILE_PICTURE_ID                                              AS PROFILE_PICTURE_ID,
              E.RETIRED_FLAG                                                    AS RETIRED_FLAG,
              E.EMPLOYEE_TYPE                                                   AS EMPLOYEE_TYPE,
              E.CREATED_BY                                                      AS CREATED_BY,
              E.MODIFIED_BY                                                     AS MODIFIED_BY,
              E.MODIFIED_DT                                                     AS MODIFIED_DT,
              INITCAP(E.FULL_NAME)                                              AS FULL_NAME,
              E.IS_HR                                                           AS IS_HR,
              E.ADDR_TEMP_ZONE_ID                                               AS ADDR_TEMP_ZONE_ID,
              E.ADDR_TEMP_DISTRICT_ID                                           AS ADDR_TEMP_DISTRICT_ID,
              E.ADDR_PERM_ZONE_ID                                               AS ADDR_PERM_ZONE_ID,
              E.ADDR_PERM_DISTRICT_ID                                           AS ADDR_PERM_DISTRICT_ID,
              E.LOCATION_ID                                                     AS LOCATION_ID,
              E.FUNCTIONAL_TYPE_ID                                              AS FUNCTIONAL_TYPE_ID,
              E.FUNCTIONAL_LEVEL_ID                                             AS FUNCTIONAL_LEVEL_ID,
              INITCAP(B.BRANCH_NAME)                                            AS BRANCH_NAME,
              INITCAP(D.DEPARTMENT_NAME)                                        AS DEPARTMENT_NAME,
              INITCAP(DES.DESIGNATION_TITLE)                                    AS DESIGNATION_TITLE,
              INITCAP(P.POSITION_NAME)                                          AS POSITION_NAME,
              P.LEVEL_NO                                                        AS LEVEL_NO,
              INITCAP(C.COMPANY_NAME)                                           AS COMPANY_NAME,
              INITCAP(G.GENDER_NAME)                                            AS GENDER_NAME,
              BG.BLOOD_GROUP_CODE                                               AS BLOOD_GROUP_CODE,
              RG.RELIGION_NAME                                                  AS RELIGION_NAME,
              INITCAP(CN.COUNTRY_NAME)                                          AS COUNTRY_NAME,
              INITCAP(VM.VDC_MUNICIPALITY_NAME)                                 AS VDC_MUNICIPALITY_NAME,
              VM1.VDC_MUNICIPALITY_NAME                                         AS VDC_MUNICIPALITY_NAME_TEMP,
              INITCAP(D1.DEPARTMENT_NAME)                                       AS APP_DEPARTMENT_NAME,
              INITCAP(DES1.DESIGNATION_TITLE)                                   AS APP_DESIGNATION_TITLE,
              INITCAP(P1.POSITION_NAME)                                         AS APP_POSITION_NAME,
              P1.LEVEL_NO                                                       AS APP_LEVEL_NO,
              INITCAP(S1.SERVICE_TYPE_NAME)                                     AS APP_SERVICE_TYPE_NAME,
              INITCAP(SE1.SERVICE_EVENT_TYPE_NAME)                              AS APP_SERVICE_EVENT_TYPE_NAME,
              LOC.LOCATION_EDESC                                                AS LOCATION_EDESC,
              FUNT.FUNCTIONAL_TYPE_EDESC                                        AS FUNCTIONAL_TYPE_EDESC,
              FUNT.FUNCTIONAL_TYPE_CODE                                         AS FUNCTIONAL_TYPE_CODE,
              FUNL.FUNCTIONAL_LEVEL_EDESC                                       AS FUNCTIONAL_LEVEL_EDESC,
              FUNL.FUNCTIONAL_LEVEL_NO                                          AS FUNCTIONAL_LEVEL_NO
            FROM HRIS_EMPLOYEES E
            LEFT JOIN HRIS_BRANCHES B
            ON E.BRANCH_ID=B.BRANCH_ID
            LEFT JOIN HRIS_DEPARTMENTS D
            ON E.DEPARTMENT_ID=D.DEPARTMENT_ID
            LEFT JOIN HRIS_DESIGNATIONS DES
            ON E.DESIGNATION_ID=DES.DESIGNATION_ID
            LEFT JOIN HRIS_POSITIONS P
            ON E.POSITION_ID=P.POSITION_ID
            LEFT JOIN HRIS_COMPANY C
            ON E.COMPANY_ID=C.COMPANY_ID
            LEFT JOIN HRIS_GENDERS G
            ON E.GENDER_ID=G.GENDER_ID
            LEFT JOIN HRIS_BLOOD_GROUPS BG
            ON E.BLOOD_GROUP_ID=BG.BLOOD_GROUP_ID
            LEFT JOIN HRIS_RELIGIONS RG
            ON E.RELIGION_ID=RG.RELIGION_ID
            LEFT JOIN HRIS_COUNTRIES CN
            ON E.COUNTRY_ID=CN.COUNTRY_ID
            LEFT JOIN HRIS_VDC_MUNICIPALITIES VM
            ON E.ADDR_PERM_VDC_MUNICIPALITY_ID=VM.VDC_MUNICIPALITY_ID
            LEFT JOIN HRIS_VDC_MUNICIPALITIES VM1
            ON E.ADDR_TEMP_VDC_MUNICIPALITY_ID=VM1.VDC_MUNICIPALITY_ID
            LEFT JOIN HRIS_DEPARTMENTS D1
            ON E.APP_DEPARTMENT_ID=D1.DEPARTMENT_ID
            LEFT JOIN HRIS_DESIGNATIONS DES1
            ON E.APP_DESIGNATION_ID=DES1.DESIGNATION_ID
            LEFT JOIN HRIS_POSITIONS P1
            ON E.APP_POSITION_ID=P1.POSITION_ID
            LEFT JOIN HRIS_SERVICE_TYPES S1
            ON E.APP_SERVICE_TYPE_ID=S1.SERVICE_TYPE_ID
            LEFT JOIN HRIS_SERVICE_EVENT_TYPES SE1
            ON E.APP_SERVICE_EVENT_TYPE_ID=SE1.SERVICE_EVENT_TYPE_ID
            LEFT JOIN HRIS_LOCATIONS LOC
            ON E.LOCATION_ID=LOC.LOCATION_ID
            LEFT JOIN HRIS_FUNCTIONAL_TYPES FUNT
            ON E.FUNCTIONAL_TYPE_ID=FUNT.FUNCTIONAL_TYPE_ID
            LEFT JOIN HRIS_FUNCTIONAL_LEVELS FUNL
            ON E.FUNCTIONAL_LEVEL_ID=FUNL.FUNCTIONAL_LEVEL_ID
            WHERE E.STATUS          ='E'
            {$condition}
            ORDER BY E.FIRST_NAME ASC";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        if ($getResult != null) {
            return $result;
        } else {
            $tempArray = [];
            foreach ($result as $item) {
                $tempObject = new HrEmployees();
                $tempObject->exchangeArrayFromDB($item);
                array_push($tempArray, $tempObject);
            }
            return $tempArray;
        }
    }

    public function fetchBy($by) {
        $orderByString=EntityHelper::getOrderBy('E.FULL_NAME ASC',null,'P.LEVEL_NO','E.JOIN_DATE','DES.ORDER_NO','E.FULL_NAME');
        $columIfSynergy="";
        $joinIfSyngery="";
        if ($this->checkIfTableExists("FA_CHART_OF_ACCOUNTS_SETUP")) {
            $columIfSynergy="FCAS.ACC_EDESC AS BANK_ACCOUNT,";
            $joinIfSyngery="LEFT JOIN FA_CHART_OF_ACCOUNTS_SETUP FCAS 
                ON(FCAS.ACC_CODE=E.ID_ACC_CODE AND C.COMPANY_CODE=FCAS.COMPANY_CODE)";
        }
        
        
        $condition = EntityHelper::getSearchConditon($by['companyId'], $by['branchId'], $by['departmentId'], $by['positionId'], $by['designationId'], $by['serviceTypeId'], $by['serviceEventTypeId'], $by['employeeTypeId'], $by['employeeId'], $by['genderId'], $by['locationId']);
        $sql = "SELECT 
            {$columIfSynergy}
                E.ID_ACCOUNT_NO  AS ID_ACCOUNT_NO,
                  E.EMPLOYEE_ID                                                AS EMPLOYEE_ID,
                  E.EMPLOYEE_CODE                                                   AS EMPLOYEE_CODE,
                  INITCAP(E.FULL_NAME)                                              AS FULL_NAME,
                  INITCAP(G.GENDER_NAME)                                            AS GENDER_NAME,
                  TO_CHAR(E.BIRTH_DATE, 'DD-MON-YYYY')                              AS BIRTH_DATE_AD,
                  BS_DATE(E.BIRTH_DATE)                                             AS BIRTH_DATE_BS,
                  TO_CHAR(E.JOIN_DATE, 'DD-MON-YYYY')                               AS JOIN_DATE_AD,
                  BS_DATE(E.JOIN_DATE)                                              AS JOIN_DATE_BS,
                  INITCAP(CN.COUNTRY_NAME)                                          AS COUNTRY_NAME,
                  RG.RELIGION_NAME                                                  AS RELIGION_NAME,
                  BG.BLOOD_GROUP_CODE                                               AS BLOOD_GROUP_CODE,
                  E.MOBILE_NO                                                       AS MOBILE_NO,
                  E.TELEPHONE_NO                                                    AS TELEPHONE_NO,
                  E.SOCIAL_ACTIVITY                                                 AS SOCIAL_ACTIVITY,
                  E.EXTENSION_NO                                                    AS EXTENSION_NO,
                  E.EMAIL_OFFICIAL                                                  AS EMAIL_OFFICIAL,
                  E.EMAIL_PERSONAL                                                  AS EMAIL_PERSONAL,
                  E.SOCIAL_NETWORK                                                  AS SOCIAL_NETWORK,
                  E.ADDR_PERM_HOUSE_NO                                              AS ADDR_PERM_HOUSE_NO,
                  E.ADDR_PERM_WARD_NO                                               AS ADDR_PERM_WARD_NO,
                  E.ADDR_PERM_STREET_ADDRESS                                        AS ADDR_PERM_STREET_ADDRESS,
                  CNP.COUNTRY_NAME                                                  AS ADDR_PERM_COUNTRY_NAME,
                  ZP.ZONE_NAME                                                      AS ADDR_PERM_ZONE_NAME,
                  DP.DISTRICT_NAME                                                  AS ADDR_PERM_DISTRICT_NAME,
                  INITCAP(VMP.VDC_MUNICIPALITY_NAME)                                AS VDC_MUNICIPALITY_NAME_PERM,
                  E.ADDR_TEMP_HOUSE_NO                                              AS ADDR_TEMP_HOUSE_NO,
                  E.ADDR_TEMP_WARD_NO                                               AS ADDR_TEMP_WARD_NO,
                  E.ADDR_TEMP_STREET_ADDRESS                                        AS ADDR_TEMP_STREET_ADDRESS,
                  CNT.COUNTRY_NAME                                                  AS ADDR_TEMP_COUNTRY_NAME,
                  ZT.ZONE_NAME                                                      AS ADDR_TEMP_ZONE_NAME,
                  DT.DISTRICT_NAME                                                  AS ADDR_TEMP_DISTRICT_NAME,
                  VMT.VDC_MUNICIPALITY_NAME                                         AS VDC_MUNICIPALITY_NAME_TEMP,
                  E.EMRG_CONTACT_NAME                                               AS EMRG_CONTACT_NAME,
                  E.EMERG_CONTACT_RELATIONSHIP                                      AS EMERG_CONTACT_RELATIONSHIP,
                  E.EMERG_CONTACT_ADDRESS                                           AS EMERG_CONTACT_ADDRESS,
                  E.EMERG_CONTACT_NO                                                AS EMERG_CONTACT_NO,
                  E.FAM_FATHER_NAME                                                 AS FAM_FATHER_NAME,
                  E.FAM_FATHER_OCCUPATION                                           AS FAM_FATHER_OCCUPATION,
                  E.FAM_MOTHER_NAME                                                 AS FAM_MOTHER_NAME,
                  E.FAM_MOTHER_OCCUPATION                                           AS FAM_MOTHER_OCCUPATION,
                  E.FAM_GRAND_FATHER_NAME                                           AS FAM_GRAND_FATHER_NAME,
                  E.FAM_GRAND_MOTHER_NAME                                           AS FAM_GRAND_MOTHER_NAME,
                  E.MARITAL_STATUS                                                  AS MARITAL_STATUS,
                  E.FAM_SPOUSE_NAME                                                 AS FAM_SPOUSE_NAME,
                  E.FAM_SPOUSE_OCCUPATION                                           AS FAM_SPOUSE_OCCUPATION,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_BIRTH_DATE, 'DD-MON-YYYY'))          AS FAM_SPOUSE_BIRTH_DATE,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_WEDDING_ANNIVERSARY, 'DD-MON-YYYY')) AS FAM_SPOUSE_WEDDING_ANNIVERSARY,
                  E.ID_CARD_NO                                                      AS ID_CARD_NO,
                  E.ID_LBRF                                                         AS ID_LBRF,
                  E.ID_BAR_CODE                                                     AS ID_BAR_CODE,
                  E.ID_PROVIDENT_FUND_NO                                            AS ID_PROVIDENT_FUND_NO,
                  E.ID_DRIVING_LICENCE_NO                                           AS ID_DRIVING_LICENCE_NO,
                  E.ID_DRIVING_LICENCE_TYPE                                         AS ID_DRIVING_LICENCE_TYPE,
                  INITCAP(TO_CHAR(E.ID_DRIVING_LICENCE_EXPIRY, 'DD-MON-YYYY'))      AS ID_DRIVING_LICENCE_EXPIRY,
                  E.ID_THUMB_ID                                                     AS ID_THUMB_ID,
                  E.ID_PAN_NO                                                       AS ID_PAN_NO,
                  E.ID_ACCOUNT_NO                                                   AS ID_ACCOUNT_NO,
                  E.ID_RETIREMENT_NO                                                AS ID_RETIREMENT_NO,
                  E.ID_CITIZENSHIP_NO                                               AS ID_CITIZENSHIP_NO,
                  INITCAP(TO_CHAR(E.ID_CITIZENSHIP_ISSUE_DATE, 'DD-MON-YYYY'))      AS ID_CITIZENSHIP_ISSUE_DATE,
                  E.ID_CITIZENSHIP_ISSUE_PLACE                                      AS ID_CITIZENSHIP_ISSUE_PLACE,
                  E.ID_PASSPORT_NO                                                  AS ID_PASSPORT_NO,
                  INITCAP(TO_CHAR(E.ID_PASSPORT_EXPIRY, 'DD-MON-YYYY'))             AS ID_PASSPORT_EXPIRY,
                  INITCAP(C.COMPANY_NAME)                                           AS COMPANY_NAME,
                  INITCAP(B.BRANCH_NAME)                                            AS BRANCH_NAME,
                  INITCAP(D.DEPARTMENT_NAME)                                        AS DEPARTMENT_NAME,
                  INITCAP(DES.DESIGNATION_TITLE)                                    AS DESIGNATION_TITLE,
                  INITCAP(P.POSITION_NAME)                                          AS POSITION_NAME,
                  P.LEVEL_NO                                                        AS LEVEL_NO,
                  INITCAP(ST.SERVICE_TYPE_NAME)                                     AS SERVICE_TYPE_NAME,
                  (CASE WHEN E.EMPLOYEE_TYPE='R' THEN 'REGULAR' ELSE 'WORKER' END)  AS EMPLOYEE_TYPE,
                  LOC.LOCATION_EDESC                                                AS LOCATION_EDESC,
                  FUNT.FUNCTIONAL_TYPE_EDESC                                        AS FUNCTIONAL_TYPE_EDESC,
                  FUNL.FUNCTIONAL_LEVEL_NO                                          AS FUNCTIONAL_LEVEL_NO,
                  FUNL.FUNCTIONAL_LEVEL_EDESC                                       AS FUNCTIONAL_LEVEL_EDESC,
                  E.SALARY                                                          AS SALARY,
                  E.SALARY_PF                                                       AS SALARY_PF,
                  E.REMARKS                                                         AS REMARKS
                FROM HRIS_EMPLOYEES E
                LEFT JOIN HRIS_COMPANY C
                ON E.COMPANY_ID=C.COMPANY_ID
                LEFT JOIN HRIS_BRANCHES B
                ON E.BRANCH_ID=B.BRANCH_ID
                LEFT JOIN HRIS_DEPARTMENTS D
                ON E.DEPARTMENT_ID=D.DEPARTMENT_ID
                LEFT JOIN HRIS_DESIGNATIONS DES
                ON E.DESIGNATION_ID=DES.DESIGNATION_ID
                LEFT JOIN HRIS_POSITIONS P
                ON E.POSITION_ID=P.POSITION_ID
                LEFT JOIN HRIS_SERVICE_TYPES ST
                ON E.SERVICE_TYPE_ID=ST.SERVICE_TYPE_ID
                LEFT JOIN HRIS_GENDERS G
                ON E.GENDER_ID=G.GENDER_ID
                LEFT JOIN HRIS_BLOOD_GROUPS BG
                ON E.BLOOD_GROUP_ID=BG.BLOOD_GROUP_ID
                LEFT JOIN HRIS_RELIGIONS RG
                ON E.RELIGION_ID=RG.RELIGION_ID
                LEFT JOIN HRIS_COUNTRIES CN
                ON E.COUNTRY_ID=CN.COUNTRY_ID
                LEFT JOIN HRIS_COUNTRIES CNP
                ON (E.ADDR_PERM_COUNTRY_ID=CNP.COUNTRY_ID)
                LEFT JOIN HRIS_ZONES ZP
                ON (E.ADDR_PERM_ZONE_ID=ZP.ZONE_ID)
                LEFT JOIN HRIS_DISTRICTS DP
                ON (E.ADDR_PERM_DISTRICT_ID=DP.DISTRICT_ID)
                LEFT JOIN HRIS_VDC_MUNICIPALITIES VMP
                ON E.ADDR_PERM_VDC_MUNICIPALITY_ID=VMP.VDC_MUNICIPALITY_ID
                LEFT JOIN HRIS_COUNTRIES CNT
                ON (E.ADDR_TEMP_COUNTRY_ID=CNT.COUNTRY_ID)
                LEFT JOIN HRIS_ZONES ZT
                ON (E.ADDR_TEMP_ZONE_ID=ZT.ZONE_ID)
                LEFT JOIN HRIS_DISTRICTS DT
                ON (E.ADDR_TEMP_DISTRICT_ID=DT.DISTRICT_ID)
                LEFT JOIN HRIS_VDC_MUNICIPALITIES VMT
                ON E.ADDR_TEMP_VDC_MUNICIPALITY_ID=VMT.VDC_MUNICIPALITY_ID
                LEFT JOIN HRIS_LOCATIONS LOC
                ON E.LOCATION_ID=LOC.LOCATION_ID
                LEFT JOIN HRIS_FUNCTIONAL_TYPES FUNT
                ON E.FUNCTIONAL_TYPE_ID=FUNT.FUNCTIONAL_TYPE_ID
                LEFT JOIN HRIS_FUNCTIONAL_LEVELS FUNL
                ON E.FUNCTIONAL_LEVEL_ID=FUNL.FUNCTIONAL_LEVEL_ID
                {$joinIfSyngery}
                WHERE 1                 =1 AND E.STATUS='E' 
                {$condition}
                {$orderByString}";
        return $this->rawQuery($sql);
    }

    public function getEmployeeListOfBirthday() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(EntityHelper::getColumnNameArrayWithOracleFns(HrEmployees::class, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [
                HrEmployees::BIRTH_DATE
            ]), false);

        $select->from("HRIS_EMPLOYEES");
        $select->where(["STATUS='E' AND RETIRED_FLAG='N'"]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $employeeList = [];

        foreach ($result as $row) {
            $today = date('d-M');
            $time = strtotime($row['BIRTH_DATE']);
            $date = date('d-M', $time);
            if ($date == $today) {
                array_push($employeeList, $row);
            }
        }
        return $employeeList;
    }

    public function getVdcMunicipalityDtl($id) {
        $result = $this->vdcGateway->select(['VDC_MUNICIPALITY_ID' => $id]);
        return $result->current();
    }

    public function getDistrictDtl($id) {
        $result = $this->districtGateway->select(['DISTRICT_ID' => $id]);
        return $result->current();
    }

    public function getZoneDtl($id) {
        $result = $this->zoneGateway->select(['ZONE_ID' => $id]);
        return $result->current();
    }

    public function fetchByEmployeeTypeWidShift($employeeType, $currentDate = null) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(EntityHelper::getColumnNameArrayWithOracleFns(HrEmployees::class, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [
                HrEmployees::BIRTH_DATE
                ], null, null, null, 'E'), false);
        $select->from(['E' => "HRIS_EMPLOYEES"])
            ->join(['SA' => ShiftAssign::TABLE_NAME], "E." . HrEmployees::EMPLOYEE_ID . "=SA." . ShiftAssign::EMPLOYEE_ID, ['EMPLOYEE_ID', 'SHIFT_ID'], 'left')
            ->join(['S' => ShiftSetup::TABLE_NAME], "SA." . ShiftAssign::SHIFT_ID . "=S." . ShiftSetup::SHIFT_ID, ['SHIFT_CODE',
                'SHIFT_ENAME' => new Expression('INITCAP(S.SHIFT_ENAME)'),
                'SHIFT_LNAME' => new Expression('INITCAP(S.SHIFT_LNAME)'),
                'START_DATE' => new Expression("INITCAP(TO_CHAR(S.START_DATE, 'DD-MON-YYYY'))"),
                'END_DATE' => new Expression("INITCAP(TO_CHAR(S.END_DATE, 'DD-MON-YYYY'))"),
                'START_TIME' => new Expression("TO_CHAR(S.START_TIME, 'HH:MI AM')"),
                'END_TIME' => new Expression("TO_CHAR(S.END_TIME, 'HH:MI AM')"),
                'HALF_TIME' => new Expression("TO_CHAR(S.HALF_TIME, 'HH:MI AM')"),
                'HALF_DAY_END_TIME' => new Expression("TO_CHAR(S.HALF_DAY_END_TIME, 'HH:MI AM')"),
                'LATE_IN' => new Expression("TO_CHAR(S.LATE_IN, 'HH24:MI')"),
                'EARLY_OUT' => new Expression("TO_CHAR(S.EARLY_OUT, 'HH24:MI')"),
                'TOTAL_WORKING_HR' => new Expression("TO_CHAR(S.TOTAL_WORKING_HR, 'HH24:MI')"),
                'ACTUAL_WORKING_HR' => new Expression("TO_CHAR(S.ACTUAL_WORKING_HR, 'HH24:MI')"),
                ], 'left')
            ->join(['AD' => AttendanceDetail::TABLE_NAME], "E." . HrEmployees::EMPLOYEE_ID . "=AD." . AttendanceDetail::EMPLOYEE_ID, [
                'IN_TIME' => new Expression("TO_CHAR(AD.IN_TIME, 'HH:MI AM')"),
                'OUT_TIME' => new Expression("TO_CHAR(AD.OUT_TIME, 'HH:MI AM')"),
                ], "left");
        if ($currentDate != null) {
            $startDate = " AND TO_DATE('" . $currentDate . "','DD-MON-YYYY') >= S.START_DATE AND TO_DATE('" . $currentDate . "','DD-MON-YYYY') <= S.END_DATE";
        } else {
            $startDate = "";
        }
        $select->where(["E.STATUS='E' AND E.RETIRED_FLAG='N' AND S.STATUS='E' AND SA.STATUS='E'", "E." . HrEmployees::EMPLOYEE_TYPE . "='" . $employeeType . "'" . $startDate]);
        $select->where([AttendanceDetail::ATTENDANCE_DT . "=TO_DATE('" . $currentDate . "','DD-MON-YYYY')"]);
        $statement = $sql->prepareStatementForSqlObject($select);
//        print_r($statement->getSql()); die();
        $result = $statement->execute();
        return $result;
    }

    public function fetchByAdminFlag() {
        $result = $this->tableGateway->select(["IS_ADMIN='Y'"]);
        return $result->current()->getArrayCopy();
    }

    public function fetchByAdminFlagList() {
        $result = $this->tableGateway->select(["IS_ADMIN='Y' AND STATUS='E'"]);
        return $result;
    }

    public function fetchEmployeeFullNameList() {
        $sql = "
            SELECT EMPLOYEE_ID AS EMPLOYEE_ID,
              INITCAP(CONCAT(CONCAT(CONCAT(LOWER(TRIM(FIRST_NAME)),' '),
              CASE
                WHEN MIDDLE_NAME IS NOT NULL
                THEN CONCAT(LOWER(TRIM(MIDDLE_NAME)), ' ')
                ELSE ''
              END ),LOWER(TRIM(LAST_NAME)))) AS FULL_NAME
            FROM HRIS_EMPLOYEES
            WHERE STATUS='E'
                ";
        $raw = EntityHelper::rawQueryResult($this->adapter, $sql);
        return Helper::extractDbData($raw);
    }

    public function vdcStringToId($districtId, $vdc) {
        if (!isset($districtId) || $districtId == null || !isset($vdc) || $vdc == null) {
            return null;
        }
        $sql = "
            SELECT VDC_MUNICIPALITY_ID
            FROM HRIS_VDC_MUNICIPALITIES
            WHERE DISTRICT_ID                      ={$districtId}
            AND LOWER(TRIM(VDC_MUNICIPALITY_NAME)) = LOWER(TRIM('{$vdc}'))";
        $result = EntityHelper::rawQueryResult($this->adapter, $sql);
        $current = $result->current();

        if ($current != null) {
            return $current['VDC_MUNICIPALITY_ID'];
        } else {
            $id = ((int) Helper::getMaxId($this->adapter, "HRIS_VDC_MUNICIPALITIES", "VDC_MUNICIPALITY_ID")) + 1;
            $insertSql = "INSERT INTO HRIS_VDC_MUNICIPALITIES (VDC_MUNICIPALITY_ID,VDC_MUNICIPALITY_NAME,DISTRICT_ID,STATUS) VALUES({$id},'{$vdc}',{$districtId},'E')";
            EntityHelper::rawQueryResult($this->adapter, $insertSql);
            return $id;
        }
    }

    public function vdcIdToString($id) {
        if (!isset($id) || $id == null) {
            return null;
        }
        $sql = "
            SELECT VDC_MUNICIPALITY_NAME
            FROM HRIS_VDC_MUNICIPALITIES
            WHERE VDC_MUNICIPALITY_ID                ={$id}";
        $result = EntityHelper::rawQueryResult($this->adapter, $sql);
        $current = $result->current();
        if ($current != null) {
            return $current['VDC_MUNICIPALITY_NAME'];
        } else {
            return null;
        }
    }

    public function fetchByHRFlagList() {
        $result = $this->tableGateway->select(["IS_HR='Y' AND STATUS='E'"]);
        $list = [];
        foreach ($result as $row) {
            array_push($list, $row['EMPLOYEE_ID']);
        }
        return (count($list) > 0) ? $list : [0];
    }

    public function employeeDetailSession($id) {
        $sql = "
                SELECT E.EMPLOYEE_ID                                                AS EMPLOYEE_ID,
                  E.COMPANY_ID                                                      AS COMPANY_ID,
                  E.EMPLOYEE_CODE                                                   AS EMPLOYEE_CODE,
                  INITCAP(E.FIRST_NAME)                                             AS FIRST_NAME,
                  INITCAP(E.MIDDLE_NAME)                                            AS MIDDLE_NAME,
                  INITCAP(E.LAST_NAME)                                              AS LAST_NAME,
                  E.NAME_NEPALI                                                     AS NAME_NEPALI,
                  E.GENDER_ID                                                       AS GENDER_ID,
                  INITCAP(TO_CHAR(E.BIRTH_DATE, 'DD-MON-YYYY'))                     AS BIRTH_DATE,
                  E.BLOOD_GROUP_ID                                                  AS BLOOD_GROUP_ID,
                  E.RELIGION_ID                                                     AS RELIGION_ID,
                  E.SOCIAL_ACTIVITY                                                 AS SOCIAL_ACTIVITY,
                  E.TELEPHONE_NO                                                    AS TELEPHONE_NO,
                  E.MOBILE_NO                                                       AS MOBILE_NO,
                  E.EXTENSION_NO                                                    AS EXTENSION_NO,
                  E.EMAIL_OFFICIAL                                                  AS EMAIL_OFFICIAL,
                  E.EMAIL_PERSONAL                                                  AS EMAIL_PERSONAL,
                  E.SOCIAL_NETWORK                                                  AS SOCIAL_NETWORK,
                  E.EMRG_CONTACT_NAME                                               AS EMRG_CONTACT_NAME,
                  E.EMERG_CONTACT_NO                                                AS EMERG_CONTACT_NO,
                  E.EMERG_CONTACT_ADDRESS                                           AS EMERG_CONTACT_ADDRESS,
                  E.EMERG_CONTACT_RELATIONSHIP                                      AS EMERG_CONTACT_RELATIONSHIP,
                  E.ADDR_PERM_HOUSE_NO                                              AS ADDR_PERM_HOUSE_NO,
                  E.ADDR_PERM_WARD_NO                                               AS ADDR_PERM_WARD_NO,
                  E.ADDR_PERM_STREET_ADDRESS                                        AS ADDR_PERM_STREET_ADDRESS,
                  E.ADDR_PERM_COUNTRY_ID                                            AS ADDR_PERM_COUNTRY_ID,
                  E.ADDR_PERM_VDC_MUNICIPALITY_ID                                   AS ADDR_PERM_VDC_MUNICIPALITY_ID,
                  E.ADDR_TEMP_HOUSE_NO                                              AS ADDR_TEMP_HOUSE_NO,
                  E.ADDR_TEMP_WARD_NO                                               AS ADDR_TEMP_WARD_NO,
                  E.ADDR_TEMP_STREET_ADDRESS                                        AS ADDR_TEMP_STREET_ADDRESS,
                  E.ADDR_TEMP_COUNTRY_ID                                            AS ADDR_TEMP_COUNTRY_ID,
                  E.ADDR_TEMP_VDC_MUNICIPALITY_ID                                   AS ADDR_TEMP_VDC_MUNICIPALITY_ID,
                  E.FAM_FATHER_NAME                                                 AS FAM_FATHER_NAME,
                  E.FAM_FATHER_OCCUPATION                                           AS FAM_FATHER_OCCUPATION,
                  E.FAM_MOTHER_NAME                                                 AS FAM_MOTHER_NAME,
                  E.FAM_MOTHER_OCCUPATION                                           AS FAM_MOTHER_OCCUPATION,
                  E.FAM_GRAND_FATHER_NAME                                           AS FAM_GRAND_FATHER_NAME,
                  E.FAM_GRAND_MOTHER_NAME                                           AS FAM_GRAND_MOTHER_NAME,
                  E.MARITAL_STATUS                                                  AS MARITAL_STATUS,
                  E.FAM_SPOUSE_NAME                                                 AS FAM_SPOUSE_NAME,
                  E.FAM_SPOUSE_OCCUPATION                                           AS FAM_SPOUSE_OCCUPATION,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_BIRTH_DATE, 'DD-MON-YYYY'))          AS FAM_SPOUSE_BIRTH_DATE,
                  INITCAP(TO_CHAR(E.FAM_SPOUSE_WEDDING_ANNIVERSARY, 'DD-MON-YYYY')) AS FAM_SPOUSE_WEDDING_ANNIVERSARY,
                  E.ID_CARD_NO                                                      AS ID_CARD_NO,
                  E.ID_LBRF                                                         AS ID_LBRF,
                  E.ID_BAR_CODE                                                     AS ID_BAR_CODE,
                  E.ID_PROVIDENT_FUND_NO                                            AS ID_PROVIDENT_FUND_NO,
                  E.ID_DRIVING_LICENCE_NO                                           AS ID_DRIVING_LICENCE_NO,
                  E.ID_DRIVING_LICENCE_TYPE                                         AS ID_DRIVING_LICENCE_TYPE,
                  INITCAP(TO_CHAR(E.ID_DRIVING_LICENCE_EXPIRY, 'DD-MON-YYYY'))      AS ID_DRIVING_LICENCE_EXPIRY,
                  E.ID_THUMB_ID                                                     AS ID_THUMB_ID,
                  E.ID_PAN_NO                                                       AS ID_PAN_NO,
                  E.ID_ACCOUNT_NO                                                   AS ID_ACCOUNT_NO,
                  E.ID_RETIREMENT_NO                                                AS ID_RETIREMENT_NO,
                  E.ID_CITIZENSHIP_NO                                               AS ID_CITIZENSHIP_NO,
                  INITCAP(TO_CHAR(E.ID_CITIZENSHIP_ISSUE_DATE, 'DD-MON-YYYY'))      AS ID_CITIZENSHIP_ISSUE_DATE,
                  E.ID_CITIZENSHIP_ISSUE_PLACE                                      AS ID_CITIZENSHIP_ISSUE_PLACE,
                  E.ID_PASSPORT_NO                                                  AS ID_PASSPORT_NO,
                  INITCAP(TO_CHAR(E.ID_PASSPORT_EXPIRY, 'DD-MON-YYYY'))             AS ID_PASSPORT_EXPIRY,
                  INITCAP(TO_CHAR(E.JOIN_DATE, 'DD-MON-YYYY'))                      AS JOIN_DATE,
                  E.SALARY                                                          AS SALARY,
                  E.SALARY_PF                                                       AS SALARY_PF,
                  E.REMARKS                                                         AS REMARKS,
                  E.STATUS                                                          AS STATUS,
                  E.CREATED_DT                                                      AS CREATED_DT,
                  E.SERVICE_EVENT_TYPE_ID                                           AS SERVICE_EVENT_TYPE_ID,
                  E.SERVICE_TYPE_ID                                                 AS SERVICE_TYPE_ID,
                  E.POSITION_ID                                                     AS POSITION_ID,
                  E.DESIGNATION_ID                                                  AS DESIGNATION_ID,
                  E.DEPARTMENT_ID                                                   AS DEPARTMENT_ID,
                  E.BRANCH_ID                                                       AS BRANCH_ID,
                  E.APP_SERVICE_EVENT_TYPE_ID                                       AS APP_SERVICE_EVENT_TYPE_ID,
                  E.APP_SERVICE_TYPE_ID                                             AS APP_SERVICE_TYPE_ID,
                  E.APP_POSITION_ID                                                 AS APP_POSITION_ID,
                  E.APP_DESIGNATION_ID                                              AS APP_DESIGNATION_ID,
                  E.APP_DEPARTMENT_ID                                               AS APP_DEPARTMENT_ID,
                  E.APP_BRANCH_ID                                                   AS APP_BRANCH_ID,
                  E.COUNTRY_ID                                                      AS COUNTRY_ID,
                  E.PROFILE_PICTURE_ID                                              AS PROFILE_PICTURE_ID,
                  E.RETIRED_FLAG                                                    AS RETIRED_FLAG,
                  E.EMPLOYEE_TYPE                                                   AS EMPLOYEE_TYPE,
                  E.CREATED_BY                                                      AS CREATED_BY,
                  E.MODIFIED_BY                                                     AS MODIFIED_BY,
                  E.MODIFIED_DT                                                     AS MODIFIED_DT,
                  INITCAP(E.FULL_NAME)                                              AS FULL_NAME,
                  E.IS_HR                                                           AS IS_HR,
                  E.ADDR_TEMP_ZONE_ID                                               AS ADDR_TEMP_ZONE_ID,
                  E.ADDR_TEMP_DISTRICT_ID                                           AS ADDR_TEMP_DISTRICT_ID,
                  E.ADDR_PERM_ZONE_ID                                               AS ADDR_PERM_ZONE_ID,
                  E.ADDR_PERM_DISTRICT_ID                                           AS ADDR_PERM_DISTRICT_ID,
                  INITCAP(B.BRANCH_NAME)                                            AS BRANCH_NAME,
                  INITCAP(C.COMPANY_NAME)                                           AS COMPANY_NAME,
                  CF.FILE_PATH                                                      AS COMPANY_FILE_PATH,
                  INITCAP(C.ADDRESS)                                                AS COMPANY_ADDRESS,
                  INITCAP(G.GENDER_NAME)                                            AS GENDER_NAME,
                  BG.BLOOD_GROUP_CODE                                               AS BLOOD_GROUP_CODE,
                  INITCAP(RG.RELIGION_NAME)                                         AS RELIGION_NAME,
                  INITCAP(CN.COUNTRY_NAME)                                          AS COUNTRY_NAME,
                  INITCAP(VM.VDC_MUNICIPALITY_NAME)                                 AS VDC_MUNICIPALITY_NAME,
                  VM1.VDC_MUNICIPALITY_NAME                                         AS VDC_MUNICIPALITY_NAME_TEMP,
                  INITCAP(D1.DEPARTMENT_NAME)                                       AS DEPARTMENT_NAME,
                  INITCAP(DES1.DESIGNATION_TITLE)                                   AS DESIGNATION_TITLE,
                  INITCAP(P1.POSITION_NAME)                                         AS POSITION_NAME,
                  P1.LEVEL_NO                                                       AS LEVEL_NO,
                  INITCAP(S1.SERVICE_TYPE_NAME)                                     AS SERVICE_TYPE_NAME,
                  INITCAP(SE1.SERVICE_EVENT_TYPE_NAME)                              AS SERVICE_EVENT_TYPE_NAME,
                  EF.FILE_PATH                                                      AS EMPLOYEE_FILE_PATH
                FROM HRIS_EMPLOYEES E
                LEFT JOIN HRIS_BRANCHES B
                ON E.BRANCH_ID=B.BRANCH_ID
                LEFT JOIN HRIS_COMPANY C
                ON E.COMPANY_ID=C.COMPANY_ID
                LEFT JOIN HRIS_GENDERS G
                ON E.GENDER_ID=G.GENDER_ID
                LEFT JOIN HRIS_BLOOD_GROUPS BG
                ON E.BLOOD_GROUP_ID=BG.BLOOD_GROUP_ID
                LEFT JOIN HRIS_RELIGIONS RG
                ON E.RELIGION_ID=RG.RELIGION_ID
                LEFT JOIN HRIS_COUNTRIES CN
                ON E.COUNTRY_ID=CN.COUNTRY_ID
                LEFT JOIN HRIS_VDC_MUNICIPALITIES VM
                ON E.ADDR_PERM_VDC_MUNICIPALITY_ID=VM.VDC_MUNICIPALITY_ID
                LEFT JOIN HRIS_VDC_MUNICIPALITIES VM1
                ON E.ADDR_TEMP_VDC_MUNICIPALITY_ID=VM1.VDC_MUNICIPALITY_ID
                LEFT JOIN HRIS_DEPARTMENTS D1
                ON E.DEPARTMENT_ID=D1.DEPARTMENT_ID
                LEFT JOIN HRIS_DESIGNATIONS DES1
                ON E.DESIGNATION_ID=DES1.DESIGNATION_ID
                LEFT JOIN HRIS_POSITIONS P1
                ON E.POSITION_ID=P1.POSITION_ID
                LEFT JOIN HRIS_SERVICE_TYPES S1
                ON E.SERVICE_TYPE_ID=S1.SERVICE_TYPE_ID
                LEFT JOIN HRIS_SERVICE_EVENT_TYPES SE1
                ON E.SERVICE_EVENT_TYPE_ID=SE1.SERVICE_EVENT_TYPE_ID
                LEFT JOIN HRIS_EMPLOYEE_FILE EF
                ON E.PROFILE_PICTURE_ID = EF.FILE_CODE
                LEFT JOIN HRIS_EMPLOYEE_FILE CF
                ON C.LOGO           =CF.FILE_CODE
                WHERE E.EMPLOYEE_ID ={$id}
";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        return $result->current();
    }

    public function setupEmployee($id) {
        $sql = "BEGIN
                  HRIS_EMPLOYEE_SETUP_PROC({$id});
                END;";
        $statement = $this->adapter->query($sql);
        $statement->execute();
    }

    public function updateJobHistory($employeeId) {
        $sql = "BEGIN
                  HRIS_UPDATE_JOB_HISTORY({$employeeId});
                END;";
        $statement = $this->adapter->query($sql);
        $statement->execute();
    }

    public function fetchBankAccountList($companyCode=null): array {
        $companyCode=($companyCode==null)?'01':$companyCode;
        if ($this->checkIfTableExists("FA_CHART_OF_ACCOUNTS_SETUP")) {
            $sql = "SELECT ACC_CODE,
                  ACC_EDESC,
                  TRANSACTION_TYPE
                FROM FA_CHART_OF_ACCOUNTS_SETUP
                WHERE ACC_NATURE  = 'AC'
                AND COMPANY_CODE  = '{$companyCode}'
                AND DELETED_FLAG  = 'N'
                AND ACC_TYPE_FLAG = 'T'";
            return $this->rawQuery($sql);
        } else {
            return [];
        }
    }
    
    public function fetchEmpowerCompany(): array{
        if ($this->checkIfTableExists("COMPANY_SETUP")) {
            $sql = "select * from COMPANY_SETUP WHERE DELETED_FLAG='N'";
            return $this->rawQuery($sql);
        } else {
            return [];
        }
    }
    
     
    public function fetchEmpowerBranch($empowerCompanyCode): array{
        if ($this->checkIfTableExists("FA_BRANCH_SETUP")) {
            $sql = "SELECT * FROM FA_BRANCH_SETUP WHERE GROUP_SKU_FLAG='I' AND DELETED_FLAG='N' AND COMPANY_CODE='{$empowerCompanyCode}'";
            return $this->rawQuery($sql);
        } else {
            return [];
        }
    }
    
    public function checkIfTableExists($tableName): bool  {
          return parent::checkIfTableExists($tableName);
    }
    
    
    public function getCompanyCodeByEmpId($employeeId){
        $sql="SELECT NVL(C.COMPANY_CODE,TO_CHAR(C.COMPANY_ID)) AS COMPANY_CODE FROM HRIS_EMPLOYEES E LEFT JOIN HRIS_COMPANY C ON (E.COMPANY_ID=C.COMPANY_ID) "
                . "WHERE E.EMPLOYEE_ID={$employeeId}";
               $statement = $this->adapter->query($sql);
                $result = $statement->execute();
                return $result->current();
    }
        
}
