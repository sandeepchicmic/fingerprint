<?php

namespace Smartwave\NewFingerprints\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

require_once BP . '/lib/internal/Mailin/mailin.php';

class Addfingerprint extends \Magento\Framework\App\Action\Action {

	public function __construct(Context $context, Session $customerSession) {
		$this->customerSession = $customerSession;
		parent::__construct($context);
	}

	public function execute() {

		if (!empty($_POST)) {
			$model = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprints');
			$imageModel = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprintsCustomerImages');
			$customerId = $this->customerSession->getCustomer()->getId();
			$deceased_name = '';
			if(isset($_POST['deceased_name'])) {
			$deceased_name = $_POST['deceased_name'];
			}
			$ID = '';
			if(isset($_POST['ID'])) {
			$ID = $_POST['ID'];
			}
			$birth_date = (!empty($_POST['birth_date'])) ? date("d/m/Y", strtotime($_POST['birth_date'])) : '';
			$customer_name = '';
			if(isset($_POST['customer_name'])) {
			$customer_name = $_POST['customer_name'];
			}
			$customer_email = '';
			if(isset($_POST['customer_email'])) {
			$customer_email = $_POST['customer_email'];
			}

			try {
				
				if($customerId) {
				$this->sendEmailOnDuplicateName($_POST, $customerId);
				}

				$data = [

					'customer_id' => $customerId, 'name' => $deceased_name, 'agent_id' => $ID, 'date' => $birth_date, 'image' => '', 'first_name' => $customer_name, 'email' => $customer_email, 'date_added' => date("Y-m-d h:i:sa")];
				$model->setData($data);
				if ($model->save()) {
					$getLastId = $model->getId();
					if (isset($_FILES['fileToUpload']) && !empty($_FILES['fileToUpload']['name'])) {
						$path = $_FILES['fileToUpload']['name'];
						$ext = pathinfo($path, PATHINFO_EXTENSION);
						$file_name = time() . '-' . mt_rand() . "." . $ext;
						copy($_FILES['fileToUpload']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/api/customer_images/' . $file_name);
						if (isset($_FILES['fileToUpload'])) {
							copy($_FILES['fileToUpload']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/api/thumbs/' . $file_name);
						}
						// $src=$_SERVER['DOCUMENT_ROOT'].'/api/customer_images/'.$file_name;
						//  $dest   = $_SERVER['DOCUMENT_ROOT'].'/api/thumbs/'.$file_name;
						//  if($ext=='png'){
						//      $source_image = imagecreatefrompng($src);
						//  }else{
						//       $source_image = imagecreatefromjpeg($src);
						//  }
						//   $width = imagesx($source_image);
						//     $height = imagesy($source_image);
						//     $desired_width=100;
						//      $desired_height = floor($height * ($desired_width / $width));
						//         $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
						//     imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
						//     if($ext=='png'){
						//         imagepng($virtual_image, $dest);
						//     }else{
						//         imagejpeg($virtual_image, $dest);
						//     }

						$data11 = ['cust_id' => $getLastId, 'image_name' => $file_name, 'image_date' => date('m/d/Y'), 'created_at' => date("Y-m-d H:i:s")];

						$imageModel->setData($data11);
						$imageModel->save();

					}

					// Create unique UPD ID
					$firstThreeInteger = rand(100000, 999999);
					if (!empty($birth_date)) {
						$getYear = date("Y", strtotime($_POST['birth_date']));
						$UPDId = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $deceased_name)) . "" . $getYear;
					} else {
						$UPDId = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $deceased_name));
					}
					$this->checkExistingUPDId($UPDId, $getLastId, $_POST); // Second parameter is deceased id

					$this->messageManager->addSuccessMessage(__('You saved the data.'));
				} else {
					$this->messageManager->addErrorMessage(__('You don\'t import correct csv file'));
				}
			} catch (Exception $e) {
				$this->messageManager->addErrorMessage(__($e->getMessage()));
			}
			$this->_redirect('newfingerprints/customer');

		}

		$this->_view->loadLayout();
		$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$resultPage->getConfig()->getTitle()->set(__('UPD Print Capture'));
		$this->_view->renderLayout();
	}

	function checkExistingUPDId($newUPDID = 0, $deceasedId = 0, $postdata = array()) {

		$status = false;
		while ($status === false) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$select = $connection->select()->from(['cd' => 'customer_detail'], ['id'])->where("unique_upd_id =?", (int) $newUPDID);
			// (int) fetches the match value with int $firstThreeInteger so giving error in loop return function checkExistingUPDId same again again.
			// so we have changed $firstThreeInteger value from tree to six digit.
			$getdata = $connection->fetchAll($select);

			if (count($getdata) > 0) {
				$firstThreeInteger = rand(100000, 999999);
				if (isset($postdata['birth_date'])) {
					$getYear = date("Y", strtotime($postdata['birth_date']));
					$newUPDID = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $postdata['deceased_name'])) . "" . $getYear;
				} else {
					$newUPDID = $firstThreeInteger . "" . strtolower(str_replace(" ", "", $postdata['deceased_name']));
				}

				return $this->checkExistingUPDId($newUPDID, $deceasedId, $postdata);
			} else {
				$status = true;
				$sql = "update customer_detail set unique_upd_id = '$newUPDID' WHERE `id` = " . $deceasedId;
				$connection->query($sql);
			}

		}

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