<?php
      namespace Smartwave\NewFingerprints\Controller\Adminhtml\Fingerprint;
      use Magento\Framework\Controller\ResultFactory;
      use Magento\Customer\Model\Session;
      use Magento\Framework\App\RequestInterface;

      class Fingerprintexport extends \Magento\Backend\App\Action
      {
        /**
        * @var \Magento\Framework\View\Result\PageFactory
        */
        protected $resultPageFactory;

        /**
         * Constructor
         *
         * @param \Magento\Backend\App\Action\Context $context
         * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
         */
        public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
             parent::__construct($context);
             $this->resultPageFactory = $resultPageFactory;
        }

        /**
         * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
         *
         * @return \Magento\Framework\View\Result\Page
         */
        public function execute()
        {
            $model = $this->_objectManager->create('Smartwave\NewFingerprints\Model\NewFingerprints');
            $data = $model->getCollection()->_getAllFingerWithCount()->getData();
            if(count($data) > 0 ){
               
                $delimiter = ",";
                $filename = "fingerprint_" . date('Y-m-d') . ".csv";
                $f = fopen('php://memory', 'w');
                $fields = array('UPD ID','Case ID(Optional)', 'Agent_email', 'Customer_id', 'Name_of_deceased', 'Date_of_birth', 'Customer_first_name', 'Customer_email', 'Status', 'date_added');
                fputcsv($f, $fields, $delimiter);
                foreach( $data as $key => $value) {
                    $status = $value['status']?'Completed':'Pending';
                    $lineData = array($value['unique_upd_id'],$value['id'], $value['agent_email'], $value['customer_id'], $value['name'], $value['date'], $value['first_name'], $value['email'], $status, $value['date_added']);
                    fputcsv($f, $lineData, $delimiter);
                }
                
                //move back to beginning of file
                fseek($f, 0);
    
                //set headers to download file rather than displayed
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '";');
    
                //output all remaining data on a file pointer
                fpassthru($f);
            }
           
            exit;
        }
      }
?>
