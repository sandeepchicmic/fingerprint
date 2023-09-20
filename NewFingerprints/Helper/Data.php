<?php

namespace Smartwave\NewFingerprints\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * fingerPrints sub folder
     * @var string
     */

    protected $fingerPrintsSubDir = 'api/customer_images';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem;
    }    
    
    /**
     * get images base url
     *
     * @return string
     */
    public function getFingerPrintsBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB]).$this->fingerPrintsSubDir.'/';
    }
   
}
