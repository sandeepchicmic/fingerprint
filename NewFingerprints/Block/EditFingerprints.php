<?php

namespace Smartwave\NewFingerprints\Block;

use Magento\Framework\View\Element\Template;
use Smartwave\NewFingerprints\Model\NewFingerprints as FingerprintsModel;


class EditFingerprints extends \Magento\Framework\View\Element\Template
{
	public $_fingerprints; 
	protected $_resourceConnection;
    protected $_connection;
	public function __construct(
		Template\Context $context, 
		array $data = array(), 
		FingerprintsModel $fingerprints,
		\Magento\Framework\App\ResourceConnection $resourceConnection
	) {
		parent::__construct($context, $data);
		$this->_fingerprints = $fingerprints;
		$this->_resourceConnection = $resourceConnection;
	}


	 /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
     
    public function getDeceasedData(){
        
        $deceasedId = $this->getRequest()->getParam('fingerprint');
        $this->_connection = $this->_resourceConnection->getConnection();
        $query = "select * FROM customer_detail where id='$deceasedId' "; 
        $collection = $this->_connection->fetchAll($query);
        return $collection;
        
    }
     
    public function getFormAction()
    {
            // companymodule is given in routes.xml
            // controller_name is folder name inside controller folder
            // action is php file name inside above controller_name folder

        //return '/SmartwaveNewFingerprints/index/booking';
        // here controller_name is index, action is booking
    }
}