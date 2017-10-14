<?php

namespace WorkOnHoliday\Controller;

use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Exception;
use HolidayManagement\Model\Holiday;
use ManagerService\Repository\HolidayWorkApproveRepository;
use SelfService\Form\WorkOnHolidayForm;
use SelfService\Model\WorkOnHoliday;
use SelfService\Repository\HolidayRepository;
use WorkOnHoliday\Repository\WorkOnHolidayStatusRepository;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element\Select;
use Zend\Mvc\Controller\AbstractActionController;

class WorkOnHolidayStatus extends AbstractActionController {

    private $adapter;
    private $holidayWorkApproveRepository;
    private $workOnHolidayStatusRepository;
    private $form;
    private $employeeId;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->holidayWorkApproveRepository = new HolidayWorkApproveRepository($adapter);
        $this->workOnHolidayStatusRepository = new WorkOnHolidayStatusRepository($adapter);
        $auth = new AuthenticationService();
        $this->employeeId = $auth->getStorage()->read()['employee_id'];
    }

    public function initializeForm() {
        $builder = new AnnotationBuilder();
        $form = new WorkOnHolidayForm();
        $this->form = $builder->createForm($form);
    }

    public function indexAction() {
        $holidayFormElement = new Select();
        $holidayFormElement->setName("holiday");
        $holidays = EntityHelper::getTableKVListWithSortOption($this->adapter, Holiday::TABLE_NAME, Holiday::HOLIDAY_ID, [Holiday::HOLIDAY_ENAME], [Holiday::STATUS => 'E'], Holiday::HOLIDAY_ENAME, "ASC", null, false, true);
        $holidays1 = [-1 => "All"] + $holidays;
        $holidayFormElement->setValueOptions($holidays1);
        $holidayFormElement->setAttributes(["id" => "holidayId", "class" => "form-control"]);
        $holidayFormElement->setLabel("Holiday Type");

        $status = [
            '-1' => 'All Status',
            'RQ' => 'Pending',
            'RC' => 'Recommended',
            'AP' => 'Approved',
            'R' => 'Rejected',
            'C' => 'Cancelled'
        ];
        $statusFormElement = new Select();
        $statusFormElement->setName("status");
        $statusFormElement->setValueOptions($status);
        $statusFormElement->setAttributes(["id" => "requestStatusId", "class" => "form-control"]);
        $statusFormElement->setLabel("Status");

        return Helper::addFlashMessagesToArray($this, [
                    'holidays' => $holidayFormElement,
                    'status' => $statusFormElement,
                    'searchValues' => EntityHelper::getSearchData($this->adapter),
        ]);
    }

    public function viewAction() {
        $this->initializeForm();
        $id = (int) $this->params()->fromRoute('id');

        if ($id === 0) {
            return $this->redirect()->toRoute("workOnHolidayStatus");
        }
        $workOnHolidayModel = new WorkOnHoliday();
        $request = $this->getRequest();

        $detail = $this->holidayWorkApproveRepository->fetchById($id);
        $status = $detail['STATUS'];
        $employeeId = $detail['EMPLOYEE_ID'];

        $requestedEmployeeID = $detail['EMPLOYEE_ID'];
        $recommApprove = $detail['RECOMMENDER_ID'] == $detail['APPROVER_ID'] ? 1 : 0;

        $employeeName = $detail['FULL_NAME'];
        $authRecommender = $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'];
        $authApprover = $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['PPROVED_BY_NAME'];

        if (!$request->isPost()) {
            $workOnHolidayModel->exchangeArrayFromDB($detail);
            $this->form->bind($workOnHolidayModel);
        } else {
            $getData = $request->getPost();
            $reason = $getData->approvedRemarks;
            $action = $getData->submit;

            $workOnHolidayModel->approvedDate = Helper::getcurrentExpressionDate();
            if ($action == "Reject") {
                $workOnHolidayModel->status = "R";
                $this->flashmessenger()->addMessage("Work on Holiday Request Rejected!!!");
            } else if ($action == "Approve") {
                try {
                    $this->wohAppAction($detail);
                    $this->flashmessenger()->addMessage("Work on Holiday Request Approved");
                } catch (Exception $e) {
                    $this->flashmessenger()->addMessage("Work on Holiday Request Approved but reward not given as position is not defined.");
                }
                $workOnHolidayModel->status = "AP";
            }
            $workOnHolidayModel->approvedBy = $this->employeeId;
            $workOnHolidayModel->approvedRemarks = $reason;
            $this->holidayWorkApproveRepository->edit($workOnHolidayModel, $id);

            return $this->redirect()->toRoute("workOnHolidayStatus");
        }
        $holidays = $this->getHolidayList($requestedEmployeeID);
        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'id' => $id,
                    'employeeId' => $employeeId,
                    'employeeName' => $employeeName,
                    'requestedDt' => $detail['REQUESTED_DATE'],
                    'recommender' => $authRecommender,
                    'approvedDT' => $detail['APPROVED_DATE'],
                    'approver' => $authApprover,
                    'status' => $status,
                    'holidays' => $holidays["holidayKVList"],
                    'holidayObjList' => $holidays["holidayList"],
                    'customRenderer' => Helper::renderCustomView(),
                    'recommApprove' => $recommApprove
        ]);
    }

    public function getHolidayList($employeeId) {
        $holidayRepo = new HolidayRepository($this->adapter);
        $holidayResult = $holidayRepo->selectAll($employeeId);
        $holidayList = [];
        $holidayObjList = [];
        foreach ($holidayResult as $holidayRow) {
            //$todayDate = new \DateTime();
            $holidayList[$holidayRow['HOLIDAY_ID']] = $holidayRow['HOLIDAY_ENAME'] . " (" . $holidayRow['START_DATE'] . " to " . $holidayRow['END_DATE'] . ")";
            $holidayObjList[$holidayRow['HOLIDAY_ID']] = $holidayRow;
        }
        return ['holidayKVList' => $holidayList, 'holidayList' => $holidayObjList];
    }

    private function wohAppAction($detail) {
        $this->holidayWorkApproveRepository->wohReward($detail['ID']);
    }

}
