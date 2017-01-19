<?php

namespace Notification\Controller;

use Application\Helper\Helper;
use Notification\Model\EmailTemplate;
use Notification\Model\LeaveRequestNotificationModel;
use Notification\Repository\EmailTemplateRepo;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

class EmailController extends AbstractActionController {

    private $employeeId;
    private $adapter;
    private $templateRepo;

    const EMAIL_TYPES = [
        1 => "Leave_Request",
        2 => "Leave_Recommend",
        3 => "Leave_Approve"];

    public function __construct(AdapterInterface $adapter) {
        $this->adapter = $adapter;
        $this->templateRepo = new EmailTemplateRepo($adapter);

        $auth = new AuthenticationService();
        $this->employeeId = $auth->getStorage()->read()['employee_id'];
    }

    public function indexAction() {
        $test = new LeaveRequestNotificationModel();
        $test->fromId = 0;
        $test->processString("[fromId]");


        $tab = (int) $this->params()->fromRoute('id');
        if ($tab == 0) {
            $tab = array_keys(self::EMAIL_TYPES)[0];
        }
        $templates = $this->templateRepo->fetchAll();
        return Helper::addFlashMessagesToArray($this, [
                    'emailTypes' => self::EMAIL_TYPES,
                    'templates' => $templates,
                    'tab' => $tab,
                    'variables' => $this->getVariables()
        ]);
    }

    private function getVariables() {
        $type1 = new LeaveRequestNotificationModel();
        return [1 => $type1->getObjectAttrs(), 2 => [], 3 => []];
    }

    public function editAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {

            $postedData = $request->getPost();
            $template = new EmailTemplate();
            $template->subject = $postedData['subject'];
            $template->description = $postedData['description'];

            $cc = [];
            foreach ($postedData['ccEmail'] as $key => $ccEmail) {
                if (isset($ccEmail) && strlen($ccEmail) > 0) {
                    array_push($cc, ['email' => $ccEmail, 'name' => $postedData['ccName'][$key]]);
                }
            }
            $bcc = [];
            foreach ($postedData['bccEmail'] as $key => $bccEmail) {
                if (isset($bccEmail) && strlen($bccEmail) > 0) {
                    array_push($bcc, ['email' => $bccEmail, 'name' => $postedData['bccName'][$key]]);
                }
            }

            $template->cc = json_encode($cc);
            $template->bcc = json_encode($bcc);

            if ($this->templateRepo->fetchById($postedData['id']) == null) {
                $template->id = $postedData['id'];
                $template->createdBy = $this->employeeId;
                $template->createdDt = Helper::getcurrentExpressionDate();
                $this->templateRepo->add($template);
            } else {
                $template->modifiedBy = $this->employeeId;
                $template->modifiedDt = Helper::getcurrentExpressionDate();
                $this->templateRepo->edit($template, $postedData['id']);
            }

            return $this->redirect()->toRoute('email', ['id' => $postedData['id']]);
        }
    }

}
