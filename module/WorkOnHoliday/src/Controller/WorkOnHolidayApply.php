<?php

namespace WorkOnHoliday\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterInterface;
use Application\Helper\Helper;
use Application\Helper\EntityHelper;
use Zend\Form\Annotation\AnnotationBuilder;
use SelfService\Form\WorkOnHolidayForm;
use Setup\Model\HrEmployees;
use SelfService\Repository\WorkOnHolidayRepository;
use SelfService\Model\WorkOnHoliday;
use HolidayManagement\Model\Holiday;

;

class WorkOnHolidayApply extends AbstractActionController {

    private $form;
    private $adapter;
    private $workOnHolidayRepository;

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->workOnHolidayRepository = new WorkOnHolidayRepository($adapter);
    }

    public function initializeForm() {
        $builder = new AnnotationBuilder();
        $form = new WorkOnHolidayForm();
        $this->form = $builder->createForm($form);
    }

    public function indexAction() {
        return $this->redirect()->toRoute("workOnHolidayStatus");
    }

    public function addAction() {
        $this->initializeForm();
        $request = $this->getRequest();

        $model = new WorkOnHoliday();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());
            if ($this->form->isValid()) {
                $model->exchangeArrayFromForm($this->form->getData());
                $model->id = ((int) Helper::getMaxId($this->adapter, WorkOnHoliday::TABLE_NAME, WorkOnHoliday::ID)) + 1;
                $model->requestedDate = Helper::getcurrentExpressionDate();
                $model->status = 'RQ';
                $this->workOnHolidayRepository->add($model);
                $this->flashmessenger()->addMessage("Work on Holiday Request Successfully added!!!");
                return $this->redirect()->toRoute("workOnHolidayStatus");
            }
        }

        return Helper::addFlashMessagesToArray($this, [
                    'form' => $this->form,
                    'employees' => EntityHelper::getTableKVListWithSortOption($this->adapter, "HRIS_EMPLOYEES", "EMPLOYEE_ID", ["FIRST_NAME", "MIDDLE_NAME", "LAST_NAME"], ["STATUS" => 'E', 'RETIRED_FLAG' => 'N'], "FIRST_NAME", "ASC", " ",false,true),
                    'holidays' => EntityHelper::getTableKVListWithSortOption($this->adapter, Holiday::TABLE_NAME, Holiday::HOLIDAY_ID, [Holiday::HOLIDAY_ENAME], ["STATUS" => 'E'], "HOLIDAY_ENAME", "ASC",null,false,true)
        ]);
    }

}
