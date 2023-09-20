<?php 
namespace Smartwave\NewFingerprints\Controller\Adminhtml\Fingerprint;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

      class Fingerprintstatus extends \Magento\Backend\App\Action
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
  $post = $this->getRequest()->getPostValue();

           $fingerprintId=$post['id'];
           $status=$post['status'];
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$tableName = $resource->getTableName('customer_detail'); //gives table name with 

$sql = "Update " . $tableName . " Set status ='".$status."' where id = $fingerprintId";
$connection->query($sql);

// $to="rsingh@chicmic.co.in";
// $nameTo="Rakashpal singh";
// $from="prsingh@chicmic.co.in";
// $nameFrom="puneet singh";
// $body="Hi Rakashpal singh";

// try{
//  $email = new \Zend_Mail();
//         $email->setSubject("Feedback email");
//         $email->setBodyText($body);
//         $email->setFrom($from, $nameFrom);
//         $email->addTo($to, $nameTo);
//         $email->send();
// }catch(\Exception $e){
// 	echo $e->getMessage();
// }

die();
             //return  $resultPage = $this->resultPageFactory->create();
        }
      }
    ?>
  