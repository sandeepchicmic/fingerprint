<?php

namespace Smartwave\NewFingerprints\Block;

use Magento\Framework\View\Element\Template;
use Smartwave\NewFingerprints\Model\NewFingerprints as FingerprintsModel;
use Magento\Customer\Model\Session;
use Smartwave\NewFingerprints\Helper\Data as FingerprintsHelper;

class NewFingerprints extends \Magento\Framework\View\Element\Template
{
	public $_fingerprints;
	public $_fingerprintsHelper;

	public function __construct(
		Template\Context $context, 
		array $data = array(), 
		FingerprintsModel $fingerprints, 
		FingerprintsHelper $fingerprintsHelper
	) {
		parent::__construct($context, $data);
		$this->_fingerprints = $fingerprints;
		$this->_fingerprintsHelper = $fingerprintsHelper;
	}

	public function getFingerprintsData() {
	    $customerId = 0;
		$collection = array();
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        if($customerSession->isLoggedIn()){
          $customerId = $customerSession->getCustomerId();
        }
        /*echo $customerId." --> In block";
        exit;*/
        
		//$customerId = $this->_customerSession->getCustomer()->getId();
		if($customerId) {
			$collection = $this->_fingerprints->getCollection()->_getFingerWithCount($customerId);
		}

		return $collection;
	}
 public function getAllUserFingerprintsData() {
		$collection = $this->_fingerprints->getCollection()->_getAllFingerWithCount();

		return $collection;
	}
	public function getFingerprintsDirectory() {

		$path = $this->_fingerprintsHelper->getFingerPrintsBaseUrl();

		return $path;
	}
	public function getImagesById(){
		 $detailId=$_GET['fingerprint'];
		$collection = $this->_fingerprints->getCollection()->_images($detailId);
		return $collection;
	}

	 /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
    public function getFormAction()
    {
            // companymodule is given in routes.xml
            // controller_name is folder name inside controller folder
            // action is php file name inside above controller_name folder

        return '/SmartwaveNewFingerprints/index/booking';
        // here controller_name is index, action is booking
    }
}