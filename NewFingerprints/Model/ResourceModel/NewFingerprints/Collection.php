<?php
namespace Smartwave\NewFingerprints\Model\ResourceModel\NewFingerprints;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

	protected $customer_images;


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smartwave\NewFingerprints\Model\NewFingerprints', 'Smartwave\NewFingerprints\Model\ResourceModel\NewFingerprints');
      
    }
    public function addCustomerIdFilter($customerId)
    {
        $result = $this->getSelect()->where("customer_id=".$customerId);
        return $result;
    }
   
    public function _getFingerWithCount($customerId)
    {
        $this->customer_images = $this->getTable("customer_images");
        $this->getSelect()
			->reset(\Zend_Db_Select::COLUMNS)	
			->columns(['id','customer_id','name','agent_id','date','image','first_name','email','date_added','status','unique_upd_id','verified_date','verified_user'])
			->joinLeft(array('detail'=>$this->customer_images), 'main_table.id=detail.cust_id',array('COUNT(detail.cust_id) AS countFinger'))->where("main_table.customer_id=".$customerId)
			->group('main_table.id');

        return $this;
    }
     public function _getAllFingerWithCount()
    {
        $this->customer_images = $this->getTable("customer_images");
        $this->getSelect()->joinInner(array('customere'=>$this->getTable('customer_entity')), 'main_table.customer_id=customere.entity_id',array('email as agent_email'));
        $this->getSelect()->joinInner(array('detail'=>$this->customer_images), 'main_table.id=detail.cust_id',array('COUNT(detail.cust_id) AS countFinger'))->group('main_table.id');

        return $this;
    }

    public function _images($customerId)
    {
        $this->customer_images = $this->getTable("customer_images");
        $this->getSelect()->join(array('detail'=>$this->customer_images), 'main_table.id=detail.cust_id',array('image_name', 'image_date', 'created_at'))->where("main_table.id=".$customerId);

        return $this;
    }

}
