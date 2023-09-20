<?php
namespace Smartwave\NewFingerprints\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;

require_once BP . '/lib/internal/Mailin/mailin.php';

class Editfingerprint extends \Magento\Framework\App\Action\Action {

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	protected $customerSession;

	/**
	 * Constructor
	 *
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->customerSession = $customerSession;
	}

	public function execute() {

		$this->_view->loadLayout();
		$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

		if (!empty($_POST)) {
			$deceasedid = $this->getRequest()->getParam('fingerprint');
			$model = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprints');
			$imageModel = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprintsCustomerImages');
			$customerId = $this->customerSession->getCustomer()->getId();

			$deceased_name = $_POST['deceased_name'];
			$ID = $_POST['ID'];
			$birth_date = (!empty($_POST['birth_date'])) ? date("m/d/Y", strtotime($_POST['birth_date'])) : '';
			$customer_name = $_POST['customer_name'];
			$customer_email = $_POST['customer_email'];

			try {

				$this->sendEmailOnDuplicateName($_POST, $customerId);

				if (!empty($deceasedid)) {
					$model->load($deceasedid);
				}
				$model->setData("name", $deceased_name);
				$model->setData("agent_id", $ID);
				$model->setData("date", $birth_date);
				$model->setData("first_name", $customer_name);
				$model->setData("email", $customer_email);
				//$model->setData($data);

				if ($model->save()) {
					$getLastId = $deceasedid;
					/*

						    	     	if(isset($_FILES['fileToUpload']) && !empty($_FILES['fileToUpload']['name'])){

						    	     	    //First delete existing image
							     	        $imageModel->setId($getLastId)->delete();

						    	     	    $path = $_FILES['fileToUpload']['name'];
						                    $ext = pathinfo($path, PATHINFO_EXTENSION);
						                 	$file_name = time().'-'.mt_rand().".".$ext;
						                 	copy($_FILES['fileToUpload']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/api/customer_images/'.$file_name);
						                 	if(isset($_FILES['fileToUpload']))
						                 	{
						                 		copy($_FILES['fileToUpload']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/api/thumbs/'.$file_name);
						                 	}

						                 	$data11=['cust_id'=>$getLastId,'image_name'=>$file_name,'image_date'=>date('m/d/Y'), 'created_at'=>date("Y-m-d H:i:s")];

						                 	$imageModel->setData($data11);
						                    $imageModel->save();

					*/
					$this->messageManager->addSuccessMessage(__('You saved the data.'));
				} else {
					$this->messageManager->addErrorMessage(__('You don\'t import correct csv file'));
				}
			} catch (Exception $e) {
				$this->messageManager->addErrorMessage(__($e->getMessage()));
			}
			$this->_redirect('newfingerprints/customer');

		}
		//return  $resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('Edit UPD Print Capture'));
		$this->_view->renderLayout();

	}

	public function sendEmailOnDuplicateName($postdata = NULL, $customerId = NULL) {
		$name = trim($postdata['deceased_name']);

		$to = "duplicateprintsupd@gmail.com";

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$select = $connection->select()->from(['cd' => 'customer_detail'], ['id'])->where("name =?", (string) $name);
		$getdata = $connection->fetchOne($select);

		$select1 = $connection->select()->from(['ce' => 'customer_entity'], ['email'])->where("entity_id =?", (int) $customerId);
		$agentEmail = $connection->fetchOne($select1);

		if (!empty($getdata) && $getdata > 0) {
			//// send email//////
			$messageBody = "Hi,";
			$messageBody .= "<p>This record has been created more than once : </p>";
			$messageBody .= "<p>" . $name . "</p>";
			$messageBody .= "<p>Agent Email : " . $agentEmail . "</p>";

			$mailin = new \Mailin('duplicateprintsupd@gmail.com', '28dtXcGQhFBHnZKA');
			$mailin->addTo($to, "Duplicate Name")->
				setFrom('duplicateprintsupd@gmail.com', 'UPDUrns')->
				setSubject('UPDUrns | Duplicate Record')->
				setHtml($messageBody);
			$mailin->send();
		}

	}

}