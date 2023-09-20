<?php
namespace Smartwave\NewFingerprints\Model;

use Magento\Framework\DataObject\IdentityInterface;

class NewFingerprints extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{ 
	const CACHE_TAG = 'customer_detail';

	protected $_cacheTag = 'customer_detail';

	protected $_eventPrefix = 'customer_detail';

	protected function _construct()
	{
		$this->_init('Smartwave\NewFingerprints\Model\ResourceModel\NewFingerprints');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}