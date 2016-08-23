<?php

namespace Setup\Controller;

/**
 * Master Setup for Service Type
 * Service Type controller.
 * Created By: Somkala Pachhai
 * Edited By: Somkala Pachhai
 * Date: August 2, 2016, Wednesday
 * Last Modified By: Somkala Pachhai
 * Last Modified Date: August 10,2016, Wednesday
 */

use Application\Helper\Helper;
use Setup\Form\ServiceTypeForm;
use Setup\Model\ServiceType;
use Setup\Repository\ServiceTypeRepository;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Mvc\Controller\AbstractActionController;

class ServiceTypeController extends AbstractActionController
{

    private $repository;
    private $form;

    function __construct(AdapterInterface $adapter)
    {
        $this->repository = new ServiceTypeRepository($adapter);
    }

    private function initializeForm()
    {
        $serviceTypeForm = new ServiceTypeForm();
        $builder = new AnnotationBuilder();
        if (!$this->form) {
            $this->form = $builder->createForm($serviceTypeForm);
        }
    }

    public function indexAction()
    {
        $serviceTypeList = $this->repository->fetchAll();
        return Helper::addFlashMessagesToArray($this, ['serviceTypeList' => $serviceTypeList]);
    }

    public function addAction()
    {

        $this->initializeForm();
        $request = $this->getRequest();

        if ($request->isPost()) {

            $this->form->setData($request->getPost());
            if ($this->form->isValid()) {
                try {
                    $serviceType=new ServiceType();
                    $serviceType->exchangeArrayFromForm($this->form->getData());
                    $serviceType->createdDt=date('d-M-y');
                    $this->repository->add($serviceType);

                    $this->flashmessenger()->addMessage("Service Type Successfully Added!!!");
                    return $this->redirect()->toRoute("serviceType");
                } catch (Exception $e) {

                }
            }
        }
        return Helper::addFlashMessagesToArray($this, [
            'form' => $this->form,
            'messages' => $this->flashmessenger()->getMessages()
        ]);
    }


    public function editAction()
    {

        $id = (int)$this->params()->fromRoute("id");
        if ($id === 0) {
            return $this->redirect()->toRoute();
        }
        $this->initializeForm();
        $request = $this->getRequest();
        $serviceType=new ServiceType();
        if (!$request->isPost()) {
            $serviceType->exchangeArrayFromDb($this->repository->fetchById($id)->getArrayCopy());
            $this->form->bind($serviceType);
        } else {

            $this->form->setData($request->getPost());
            if ($this->form->isValid()) {
                $serviceType->exchangeArrayFromForm($this->form->getData());
                $serviceType->modifiedDt=date('d-M-y');
                $this->repository->edit($serviceType, $id);
                $this->flashmessenger()->addMessage("Service Type Successfully Updated!!!");
                return $this->redirect()->toRoute("serviceType");
            }
        }
        return Helper::addFlashMessagesToArray($this, ['form' => $this->form, 'id' => $id]);
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute("id");

        if (!$id) {
            return $this->redirect()->toRoute('serviceType');
        }
        $this->repository->delete($id);
        $this->flashmessenger()->addMessage("Service Type Successfully Deleted!!!");
        return $this->redirect()->toRoute('serviceType');
    }
}

/* End of file ServiceTypeController.php */
/* Location: ./Setup/src/Controller/ServiceTypeController.php */

?>

