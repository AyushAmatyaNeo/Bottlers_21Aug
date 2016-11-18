<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11/3/16
 * Time: 11:11 AM
 */
namespace SelfService\Controller;

use Application\Helper\Helper;
use Setup\Repository\EmployeeRepository;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Annotation\AnnotationBuilder;
use Setup\Form\HrEmployeesFormTabFive;
use Setup\Form\HrEmployeesFormTabFour;
use Setup\Form\HrEmployeesFormTabOne;
use Setup\Form\HrEmployeesFormTabThree;
use Setup\Form\HrEmployeesFormTabTwo;
use Setup\Repository\EmployeeFile;
use Application\Helper\EntityHelper as ApplicationHelper;
use Setup\Helper\EntityHelper;

class Profile extends AbstractActionController {

    private $adapter;
    private $repository;
    private $formOne;
    private $formTwo;
    private $formThree;
    private $formFour;
    private $formFive;
    private $employeeId;
    private $employeeFileRepo;
    const UPLOAD_DIR = "/var/www/html/neo_hris/public/uploads/";

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->repository = new EmployeeRepository($adapter);
        $this->employeeFileRepo=new EmployeeFile($this->adapter);

        $this->authService = new AuthenticationService();
        $recordDetail = $this->authService->getIdentity();
        $this->employeeId = $recordDetail['employee_id'];
    }

    public function initializeForm()
    {
        $builder = new AnnotationBuilder();
        $formTabOne = new HrEmployeesFormTabOne();
        $formTabTwo = new HrEmployeesFormTabTwo();
        $formTabThree = new HrEmployeesFormTabThree();
        $formTabFour = new HrEmployeesFormTabFour();
        $formTabFive = new HrEmployeesFormTabFive();

        if (!$this->formOne) {
            $this->formOne = $builder->createForm($formTabOne);
        }
        if (!$this->formTwo) {
            $this->formTwo = $builder->createForm($formTabTwo);
        }
        if (!$this->formThree) {
            $this->formThree = $builder->createForm($formTabThree);
        }
        if (!$this->formFour) {
            $this->formFour = $builder->createForm($formTabFour);
        }
        if (!$this->formFive) {
            $this->formFive = $builder->createForm($formTabFive);
        }

    }

    public function indexAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (0 === $id) {
            $id = $this->employeeId;
        }

        $tab = (int)$this->params()->fromRoute('tab', 0);
        if (0 === $tab) {
            $tab=1;
        }

        $this->initializeForm();
        $request = $this->getRequest();

        $formOneModel = new HrEmployeesFormTabOne();
        $formTwoModel = new HrEmployeesFormTabTwo();
        $formThreeModel = new HrEmployeesFormTabThree();
        $formFourModel = new HrEmployeesFormTabFour();
        $formFiveModel = new HrEmployeesFormTabFive();

        $employeeData = (array)$this->repository->fetchById($id);

        $employeeFile=(array) $this->employeeFileRepo->fetchById($id);

        if ($request->isPost()) {
            $postData = $request->getPost();
            switch ($tab) {
                case 1:
                    $this->formOne->setData($postData);
                    if ($this->formOne->isValid()) {
                        $formOneModel->exchangeArrayFromForm($this->formOne->getData());
                        $formOneModel->birthDate = Helper::getExpressionDate($formOneModel->birthDate);
                        $formOneModel->addrPermCountryId = 168;
                        $formOneModel->addrTempCountryId = 168;
                        $this->repository->edit($formOneModel, $id);
                        return $this->redirect()->toRoute('profile', ['action' => 'index', 'id' => $id, 'tab' => 2]);
                    }
                    break;
                case 2:
                    $this->formTwo->setData($postData);
                    if ($this->formTwo->isValid()) {
                        $formTwoModel->exchangeArrayFromForm($this->formTwo->getData());
                        $formTwoModel->famSpouseBirthDate = Helper::getExpressionDate($formTwoModel->famSpouseBirthDate);
                        $formTwoModel->famSpouseWeddingAnniversary = Helper::getExpressionDate($formTwoModel->famSpouseWeddingAnniversary);;
                        $this->repository->edit($formTwoModel, $id);
                        return $this->redirect()->toRoute('profile', ['action' => 'index', 'id' => $id, 'tab' => 3]);
                    }
                    break;
                case 3:
                    $this->formThree->setData($postData);
                    if ($this->formThree->isValid()) {
                        $formThreeModel->exchangeArrayFromForm($this->formThree->getData());
                        $formThreeModel->idDrivingLicenseExpiry = Helper::getExpressionDate($formThreeModel->idDrivingLicenseExpiry);
                        $formThreeModel->idCitizenshipIssueDate = Helper::getExpressionDate($formThreeModel->idCitizenshipIssueDate);
                        $formThreeModel->idPassportExpiry = Helper::getExpressionDate($formThreeModel->idPassportExpiry);
                        $this->repository->edit($formThreeModel, $id);
                        return $this->redirect()->toRoute('profile', ['action' => 'index', 'id' => $id, 'tab' => 4]);
                    }
                    break;
                case 4:
                    $this->formFour->setData($postData);
                    if ($this->formFour->isValid()) {
                        $formFourModel->exchangeArrayFromForm($this->formFour->getData());
                        $this->repository->edit($formFourModel, $id);
                        return $this->redirect()->toRoute('profile', ['action' => 'index', 'id' => $id, 'tab' => 5]);
                    }
                    break;
                case 5:
                    $post = array_merge_recursive(
                        $postData->toArray(),
                        $request->getFiles()->toArray()
                    );
                    $this->formFive->setData($post);
                    if ($this->formFive->isValid()) {
                        $uploadedFile = $post['filePath'];
                        $ext = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
                        $newFileName = Helper::generateUniqueName() . "." . $ext;
                        $success = move_uploaded_file($uploadedFile['tmp_name'],self::UPLOAD_DIR. $newFileName );
                        if ($success) {
                            $formFiveModel->fileCode=((int) Helper::getMaxId($this->adapter,'HR_EMPLOYEE_FILE','FILE_CODE'))+1;
                            $formFiveModel->employeeId=$id;
                            $formFiveModel->fileTypeCode=$post['fileTypeCode'];
                            $formFiveModel->filePath=$newFileName;
                            $formFiveModel->status='E';
                            $formFiveModel->createdDt=Helper::getcurrentExpressionDate();

                            $this->employeeFileRepo->add($formFiveModel);
                            return $this->redirect()->toRoute('profile', ['action' => 'index', 'id' => $id, 'tab' => 6]);
                        }

                    }
                    break;
            }

        }
        if ($tab != 1 || !$request->isPost()) {
            $formOneModel->exchangeArrayFromDB($employeeData);
            $this->formOne->bind($formOneModel);
        }

        if ($tab != 2 || !$request->isPost()) {
            $formTwoModel->exchangeArrayFromDB($employeeData);
            $this->formTwo->bind($formTwoModel);
        }

        if ($tab != 3 || !$request->isPost()) {
            $formThreeModel->exchangeArrayFromDB($employeeData);
            $this->formThree->bind($formThreeModel);
        }

        if ($tab != 4 || !$request->isPost()) {
            $formFourModel->exchangeArrayFromDB($employeeData);
            $this->formFour->bind($formFourModel);
        }
        if ($tab != 5 || !$request->isPost()) {
            $formFiveModel->exchangeArrayFromDB($employeeFile);
//            print "<pre>";
////            print_r($formFiveModel);
            $this->formFive->bind($formFiveModel);
//            print_r($this->formFive->get('filePath'));
//            exit;
        }

        return Helper::addFlashMessagesToArray($this, [
            'formOne' => $this->formOne,
            'formTwo' => $this->formTwo,
            'formThree' => $this->formThree,
            'formFour' => $this->formFour,
            'formFive' => $this->formFive,
            'tab' => $tab,
            "id" => $id,
            "bloodGroups" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_BLOOD_GROUPS),
            "districts" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_DISTRICTS),
            "genders" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_GENDERS),
            "vdcMunicipalities" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_VDC_MUNICIPALITY),
            "zones" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_ZONES),
            "religions" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_RELIGIONS),
            "companies" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_COMPANY),
            "countries" => EntityHelper::getTableKVList($this->adapter, EntityHelper::HR_COUNTRIES),
            'filetypes'=>EntityHelper::getTableKVList($this->adapter,EntityHelper::HR_FILE_TYPE),
            'serviceTypes'=>ApplicationHelper::getTableKVList($this->adapter,"HR_SERVICE_TYPES","SERVICE_TYPE_ID",["SERVICE_TYPE_NAME"]),
            'positions'=>ApplicationHelper::getTableKVList($this->adapter,"HR_POSITIONS","POSITION_ID",["POSITION_NAME"]),
            'designations'=>ApplicationHelper::getTableKVList($this->adapter,"HR_DESIGNATIONS","DESIGNATION_ID",["DESIGNATION_TITLE"]),
            'departments'=>ApplicationHelper::getTableKVList($this->adapter,"HR_DEPARTMENTS","DEPARTMENT_ID",["DEPARTMENT_NAME"]),
            'branches'=>ApplicationHelper::getTableKVList($this->adapter,"HR_BRANCHES","BRANCH_ID",["BRANCH_NAME"])
        ]);
    }
}