<?php
namespace Smartwave\NewFingerprints\Controller\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

require_once BP . '/lib/internal/Mailin/mailin.php';

class Index extends \Magento\Framework\App\Action\Action {

	public function __construct(Context $context, Session $customerSession) {
		$this->customerSession = $customerSession;
		parent::__construct($context);
	}

	public function execute() {

		if (!empty($_FILES)) {
			//print_r($_FILES);
			try {
				//print_r($_FILES);
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				//$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/vnd.ms-excel', 'application/octet-stream', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'text/plain');
				if (!empty($_FILES['import_csv']['name']) && in_array($_FILES['import_csv']['type'], $csvMimes)) {
					if (is_uploaded_file($_FILES['import_csv']['tmp_name'])) {
						$csvFile = fopen($_FILES['import_csv']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$model = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprints');
						$customerId = $this->customerSession->getCustomer()->getId();
						$deceased_name = "";
						$birth_date = "";
						$x = 0;
						$nameArr = [];
						while (($line = fgetcsv($csvFile)) !== FALSE) {
							if (!empty($line[1]) || !empty($line[2]) || !empty($line[3])) {

								$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
								$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
								$connection = $resource->getConnection();
								$select = $connection->select()->from(['cd' => 'customer_detail'], ['id'])->where("name =?", (string) $line[1]);
								$getdata = $connection->fetchOne($select);

								if (!empty($getdata) && $getdata > 0) {
									if (!in_array(trim($line[1]), $nameArr)) {
										$nameArr[$x] = $line[1];
									}
									$x++;
								}

								$data = [

									'customer_id' => $customerId, 'name' => isset($line[1]) ? $line[1] : '', 'agent_id' => isset($line[0]) ? $line[0] : '', 'date' => isset($line[4]) ? $line[4] : date('Y-m-d'), 'image' => '', 'first_name' => isset($line[2]) ? $line[2] : '', 'email' => isset($line[3]) ? $line[3] : '', 'date_added' => date("Y-m-d h:i:sa")];
								$model->setData($data);
								$model->save();
								$getLastId = $model->getId();
								if (!empty($getLastId) && isset($getLastId)) {
									// Create unique UPD ID
									$firstThreeInteger = rand(100, 999);
									$deceased_name = $line[1];
									$birth_date = $line[4];
									if (!empty($birth_date)) {
										$getYear = date("Y", strtotime($birth_date));
										$UPDId = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $deceased_name)) . "" . $getYear;
									} else {
										$UPDId = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $deceased_name));
									}
									$this->checkExistingUPDId($UPDId, $getLastId, $line); // Second parameter is deceased id
								}

							}
						}

						$this->sendEmailOnDuplicateName($nameArr, $customerId);
					}
					$this->messageManager->addSuccessMessage(__('You saved the data.'));
				} else {
					$csv_link = "https://updurns.com/pub/static/version1543751774/frontend/Smartwave/porto/en_US/Smartwave_NewFingerprints/images/importcsv.csv";
					$noticeMsg = __('Incorrect csv file format! <a href="' . $csv_link . '">Click here</a> to download csv sample. ');
					$this->messageManager->addError($noticeMsg);
				}

				// $model = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprints');
				// $customerId = $this->customerSession->getCustomer()->getId();
				//     $data=[
				//         'customer_id'=>5661,'name'=>"Rakashpal",'agent_id'=>'','date'=>'2017-7-26','image'=>'dfasdf','first_name'=>'Rakashpal Singh','email'=>'rsingh@chicmic.co.in','date_added'=>'2017-7-26'];
				//      $model->setData($data);
				// if($model->save()){

			} catch (Exception $e) {
				$this->messageManager->addErrorMessage(__($e->getMessage()));
			}
			// }else{
			//      $this->messageManager->addErrorMessage(__('Data was not saved.'));
			// }
			$this->_redirect('newfingerprints/customer/');
		} // files if end here

		$this->_view->loadLayout();
		$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$resultPage->getConfig()->getTitle()->set(__('NewFingerprints'));
		$this->_view->renderLayout();
	}

	function checkExistingUPDId($newUPDID = 0, $deceasedId = 0, $postdata = array()) {

		$status = false;
		while ($status === false) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$select = $connection->select()->from(['cd' => 'customer_detail'], ['id'])->where("unique_upd_id =?", (int) $newUPDID);
			$getdata = $connection->fetchAll($select);

			if (count($getdata) > 0) {
				$firstThreeInteger = rand(100, 999);
				if (!empty($postdata[4])) {
					$getYear = date("Y", strtotime($postdata[4]));
					$newUPDID = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $postdata[1])) . "" . $getYear;
				} else {
					$newUPDID = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $postdata[1]));
				}

				return $this->checkExistingUPDId($newUPDID, $deceasedId, $postdata);
			} else {
				$status = true;
				$sql = "update customer_detail set unique_upd_id = '$newUPDID' WHERE `id` = " . $deceasedId;
				$connection->query($sql);
			}

		}

	}

	public function sendEmailOnDuplicateName($array = NULL, $customerId = NULL) {

		$to = "duplicateprintsupd@gmail.com";

		if (!empty($array)) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$select = $connection->select()->from(['ce' => 'customer_entity'], ['email'])->where("entity_id =?", (int) $customerId);
			$agentEmail = $connection->fetchOne($select);

			$names = "";
			foreach ($array as $val) {
				$names .= $val . ", ";
			}
			$names = substr($names, 0, -2);

			//// send email//////
			$messageBody = "Hi,";
			$messageBody .= "<p>This record has been created more than once : </p>";
			$messageBody .= "<p>" . $names . "</p>";
			$messageBody .= "<p>Agent Email : " . $agentEmail . "</p>";

			$mailin = new \Mailin('duplicateprintsupd@gmail.com', '28dtXcGQhFBHnZKA');
			$mailin->addTo($to, "Duplicate Name")->
				setFrom('duplicateprintsupd@gmail.com', 'UPDUrns')->
				setSubject('UPDUrns | Duplicate Record')->
				setHtml($messageBody);
			$mailin->send();
		}

	}

	/**
	 * Check customer authentication
	 *
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @return \Magento\Framework\App\ResponseInterface
	 */
	public function dispatch(RequestInterface $request) {
		if (!$this->customerSession->authenticate()) {
			$this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
		}
		return parent::dispatch($request);
	}

}