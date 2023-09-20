<?php
namespace Smartwave\NewFingerprints\Model;

use Magento\Framework\DataObject\IdentityInterface;

class NewFingerprintsCustomerImages extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{ 
	const CACHE_TAG = 'customer_images';

	protected $_cacheTag = 'customer_images';

	protected $_eventPrefix = 'customer_images';

	protected function _construct()
	{
		$this->_init('Smartwave\NewFingerprints\Model\ResourceModel\NewFingerprintsCustomerImages');
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