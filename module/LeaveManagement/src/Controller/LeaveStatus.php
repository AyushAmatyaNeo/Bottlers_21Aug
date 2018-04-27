<?php
namespace LeaveManagement\Controller;

use Application\Controller\HrisController;
use Application\Helper\EntityHelper;
use Application\Helper\Helper;
use Exception;
use LeaveManagement\Form\LeaveApplyForm;
use LeaveManagement\Model\LeaveApply;
use LeaveManagement\Model\LeaveMaster;
use LeaveManagement\Repository\LeaveStatusRepository;
use ManagerService\Repository\LeaveApproveRepository;
use Notification\Controller\HeadNotification;
use Notification\Model\NotificationEvents;
use SelfService\Repository\LeaveRequestRepository;
use Setup\Model\HrEmployees;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;

class LeaveStatus extends HrisController {

    public function __construct(AdapterInterface $adapter, StorageInterface $storage) {
        parent::__construct($adapter, $storage);
        $this->initializeRepository(LeaveStatusRepository::class);
        $this->initializeForm(LeaveApplyForm::class);
    }

    public function indexAction() {
        $leaveList = EntityHelper::getTableKVListWithSortOption($this->adapter, LeaveMaster::TABLE_NAME, LeaveMaster::LEAVE_ID, [LeaveMaster::LEAVE_ENAME], [LeaveMaster::STATUS => 'E'], LeaveMaster::LEAVE_ENAME, "ASC", NULL, ['-1' => 'All Leaves'], TRUE);
        $leaveSE = $this->getSelectElement(['name' => 'leave', 'id' => 'leaveId', 'class' => 'form-control', 'label' => 'Type'], $leaveList);
        $leaveStatusSE = $this->getStatusSelectElement(['name' => 'leaveStatus', 'id' => 'leaveRequestStatusId', 'class' => 'form-control', 'label' => 'Status']);

        return $this->stickFlashMessagesTo([
                'leaves' => $leaveSE,
                'leaveStatus' => $leaveStatusSE,
                'searchValues' => EntityHelper::getSearchData($this->adapter),
                'acl' => $this->acl,
                'employeeDetail' => $this->storageData['employee_detail']
        ]);
    }

    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id');

        if ($id === 0) {
            return $this->redirect()->toRoute("leavestatus");
        }
        $request = $this->getRequest();
        $leaveRequestRepository = new LeaveRequestRepository($this->adapter);
        $leaveApproveRepository = new LeaveApproveRepository($this->adapter);


        $detail = $leaveApproveRepository->fetchById($id);

        $status = $detail['STATUS'];

        $requestedEmployeeID = $detail['EMPLOYEE_ID'];
        $recommApprove = $detail['RECOMMENDER_ID'] == $detail['APPROVER_ID'] ? 1 : 0;

        $employeeName = $detail['FULL_NAME'];
        $authRecommender = $detail['RECOMMENDED_BY_NAME'] == null ? $detail['RECOMMENDER_NAME'] : $detail['RECOMMENDED_BY_NAME'];
        $authApprover = $detail['APPROVED_BY_NAME'] == null ? $detail['APPROVER_NAME'] : $detail['APPROVED_BY_NAME'];

        //to get the previous balance of selected leave from assigned leave detail
        $preBalance = $detail['BALANCE'];

        $leaveApply = new LeaveApply();
        if ($request->isPost()) {
            $getData = $request->getPost();
            $reason = $getData->approvedRemarks;
            $action = $getData->submit;

            $leaveApply->approvedDt = Helper::getcurrentExpressionDate();
            if ($action == "Reject") {
                $leaveApply->status = "R";
                $this->flashmessenger()->addMessage("Leave Request Rejected!!!");
            } else if ($action == "Approve") {
                $leaveApply->status = "AP";
                $this->flashmessenger()->addMessage("Leave Request Approved");
            }
            unset($leaveApply->halfDay);
            $leaveApply->approvedRemarks = $reason;
            $leaveApply->approvedBy = $this->employeeId;
            $leaveApproveRepository->edit($leaveApply, $id);

            return $this->redirect()->toRoute("leavestatus");
        }
        $leaveApply->exchangeArrayFromDB($detail);
        $this->form->bind($leaveApply);
        return Helper::addFlashMessagesToArray($this, [
                'form' => $this->form,
                'id' => $id,
                'employeeId' => $requestedEmployeeID,
                'employeeName' => $employeeName,
                'requestedDt' => $detail['REQUESTED_DT'],
                'availableDays' => $preBalance,
                'totalDays' => $detail['TOTAL_DAYS'],
                'recommender' => $authRecommender,
                'approver' => $authApprover,
                'approvedDT' => $detail['APPROVED_DT'],
                'remarkDtl' => $detail['REMARKS'],
                'status' => $status,
                'allowHalfDay' => $detail['ALLOW_HALFDAY'],
                'leave' => $leaveRequestRepository->getLeaveList($detail['EMPLOYEE_ID']),
                'customRenderer' => Helper::renderCustomView(),
                'recommApprove' => $recommApprove,
                'subEmployeeId' => $detail['SUB_EMPLOYEE_ID'],
                'subRemarks' => $detail['SUB_REMARKS'],
                'subApprovedFlag' => $detail['SUB_APPROVED_FLAG'],
                'employeeList' => EntityHelper::getTableKVListWithSortOption($this->adapter, HrEmployees::TABLE_NAME, HrEmployees::EMPLOYEE_ID, [HrEmployees::FIRST_NAME, HrEmployees::MIDDLE_NAME, HrEmployees::LAST_NAME], [HrEmployees::STATUS => "E", HrEmployees::RETIRED_FLAG => "N"], HrEmployees::FIRST_NAME, "ASC", " ", FALSE, TRUE),
                'gp' => $detail['GRACE_PERIOD']
        ]);
    }

    public function pullLeaveRequestStatusListAction() {
        try {
            $request = $this->getRequest();
            $data = $request->getPost();
            $result = $this->repository->getLeaveRequestList($data);

            $recordList = Helper::extractDbData($result);
            return new JsonModel([
                "success" => "true",
                "data" => $recordList,
                "message" => null
            ]);
        } catch (Exception $e) {
            return new JsonModel(['success' => false, 'data' => null, 'message' => $e->getMessage()]);
        }
    }

    public function bulkAction() {
        $request = $this->getRequest();
        try {
            $postData = $request->getPost();
            $this->makeDecision($postData['id'], $postData['action'] == "approve");
            return new JsonModel(['success' => true, 'data' => null]);
        } catch (Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function makeDecision($id, $approve, $remarks = null, $enableFlashNotification = false) {
        $model = new LeaveApply();
        $model->id = $id;
        $model->recommendedDate = Helper::getcurrentExpressionDate();
        $model->recommendedBy = $this->employeeId;
        $model->approvedRemarks = $remarks;
        $model->approvedDate = Helper::getcurrentExpressionDate();
        $model->approvedBy = $this->employeeId;
        $model->status = $approve ? "AP" : "R";
        $message = $approve ? "Leave Request Approved" : "Leave Request Rejected";
        $notificationEvent = $approve ? NotificationEvents::LEAVE_APPROVE_ACCEPTED : NotificationEvents::LEAVE_APPROVE_REJECTED;
        $leaveApproveRepository = new LeaveApproveRepository($this->adapter);
        $leaveApproveRepository->edit($model, $id);
        if ($enableFlashNotification) {
            $this->flashmessenger()->addMessage($message);
        }
        try {
            HeadNotification::pushNotification($notificationEvent, $model, $this->adapter, $this);
        } catch (Exception $e) {
            $this->flashmessenger()->addMessage($e->getMessage());
        }
    }
}
