<?php

namespace Smartwave\NewFingerprints\Controller\Adminhtml\Fingerprint;

//use Magento\Framework\App\ObjectManager;

use Smartwave\NewFingerprints\Model\NewFingerprints as FingerprintsModel;
use Magento\Customer\Model\Session;
use Smartwave\NewFingerprints\Helper\Data as FingerprintsHelper;

class Records extends \Magento\Backend\App\Action
{
    
  /**
  * @var \Magento\Framework\View\Result\PageFactory
  */
  protected $resultPageFactory;

  protected $_fingerprints;
  protected $_fingerprintsHelper;
  protected $resultJsonFactory;
  protected $_urlInterface;

  private $totalProducts;
  private $filteredProducts;
  private $draw;

  /**
   * Constructor
   *
   * @param \Magento\Backend\App\Action\Context $context
   * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
   */
  public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
      \Magento\Framework\UrlInterface $urlInterface,
      FingerprintsModel $fingerprints,
      FingerprintsHelper $fingerprintsHelper
  ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_fingerprints = $fingerprints;
        $this->_fingerprintsHelper = $fingerprintsHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_urlInterface = $urlInterface;
  }

  /**
   * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
   *
   * @return \Magento\Framework\View\Result\Page
   */
  public function execute( ) { 

    $returnData         = [ 
                            "draw"            => 1,
                            "recordsTotal"    => 0,
                            "recordsFiltered" => 0,
                            'data'            => []
                          ];

    $fingerprintRecords = $this->getAllUserFingerprintsData( )->getData( );
    $resultJson         = $this->resultJsonFactory->create( );

    if ( empty( $fingerprintRecords ) ) { 
      $returnData[ 'draw' ] = $this->draw;
      $resultJson->setData( $returnData );
      return $resultJson;
    }

    $returnData[ 'recordsTotal' ] = $this->totalProducts;
    $returnData[ 'recordsFiltered' ] = $this->filteredProducts;
    $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
    $timezoneInterface  = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
    
    foreach ( $fingerprintRecords as $key => $value ) { 

      $data = [
          '#' . $value[ 'id' ],
          $value[ 'agent_id' ],
          ( $value[ 'unique_upd_id' ] != "" ? $value[ 'unique_upd_id' ] : "-" ),
          $value[ 'agent_email' ],
          $value[ 'name' ],
          $value[ 'date' ],
          $value[ 'first_name' ],
          $value[ 'email' ],
          $timezoneInterface->date( $value[ 'date_added' ] )->format( 'm/d/Y' ),
          ( $value[ 'verified_date' ] != "" ? $timezoneInterface->date( $value[ 'verified_date' ] )->format( 'm/d/Y' ) : "-" )
      ];

      if ( $value[ 'countFinger' ] > 0 ) { 
        $href= $this->_urlInterface->getUrl() . 'admin_72c7y6/adminfingerprint/fingerprint/?fingerprint=' . $value[ 'id' ]; 
        $data[ ] = '<select class="fingerprint-status" data-fingerid="' . $value[ 'id' ] .'"><option value="0"' . ( $value[ 'status' ] ? '' : ' selected="selected"' ) . '>Pending</option><option value="1"' . ( $value[ 'status' ] ? ' selected="selected"' : '' ) . '>Completed</option></select><a onclick="setimgs(\'' . $href . '\');" style="width:30px;margin:auto;display:block"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 54.308 54.308" style="enable-background:new 0 0 54.308 54.308;" xml:space="preserve" class=""><g><g><path d="M53.583,25.902c-5.447-9.413-15.574-15.26-26.429-15.26S6.173,16.489,0.725,25.902L0,27.154   l0.725,1.252c5.447,9.413,15.574,15.26,26.429,15.26s20.981-5.847,26.429-15.26l0.725-1.252L53.583,25.902z M27.425,36.032   c-5.342,0-9.688-4.346-9.688-9.688s4.346-9.688,9.688-9.688s9.688,4.346,9.688,9.688S32.767,36.032,27.425,36.032z M5.826,27.154   c2.304-3.497,5.412-6.325,8.99-8.306c-1.312,2.198-2.08,4.756-2.08,7.496c0,3.911,1.546,7.459,4.046,10.094   C12.377,34.469,8.542,31.276,5.826,27.154z M38.479,35.985c2.256-2.583,3.634-5.95,3.634-9.641c0-2.537-0.646-4.925-1.783-7.009   c3.225,1.948,6.03,4.599,8.151,7.819C45.964,30.975,42.483,33.995,38.479,35.985z" data-original="#010002" class="active-path" data-old_color="#ABA4B2" fill="#656068"/></g></g></svg></a>';
      } else { 
        $data[ ] = "<span class='no-image'>No-Image</span>";
      }

      $returnData[ 'data' ][] = $data;
    }

    $returnData[ 'draw' ] = $this->draw;

    $resultJson = $this->resultJsonFactory->create();
    $resultJson->setData( $returnData );
    return $resultJson;
  }

	private function getAllUserFingerprintsData( ) {
	    
    $collection       = $this->_fingerprints->getCollection( )->_getAllFingerWithCount( );
    $filterCollection = clone $collection;

    $this->totalProducts = $collection->getSize( );

    $this->draw       = ( $this->getRequest( )->getParam( 'draw' ) ) ? $this->getRequest( )->getParam( 'draw' ) : 1;
    $page             = ( $this->getRequest( )->getParam( 'start' ) ) ? $this->getRequest( )->getParam( 'start' ) : 1;
    $pageSize         = ( $this->getRequest( )->getParam( 'length' ) ) ? $this->getRequest( )->getParam( 'length' ) : 10;
    $search           = ( $this->getRequest( )->getParam( 'search' ) ) ? $this->getRequest( )->getParam( 'search' ) : '';
    $order            = ( $this->getRequest( )->getParam( 'order' ) ) ? $this->getRequest( )->getParam( 'order' ) : '';

    if( $search && isset( $search[ 'value' ] ) && $search[ 'value' ] ) { 
      $filterCollection->addFieldToFilter(['customere.email', 'name', 'first_name', 'main_table.email'],
          [
              ['like' => '%' . $search[ 'value' ] . '%'],
              ['like' => '%' . $search[ 'value' ] . '%'],
              ['like' => '%' . $search[ 'value' ] . '%'],
              ['like' => '%' . $search[ 'value' ] . '%']
          ]);

          $this->filteredProducts = $filterCollection->getSize( );
    } else { 
      $this->filteredProducts = $this->totalProducts;
    }

    if( $order && isset( $order[ 0 ] ) && isset( $order[ 0 ][ 'column' ] ) && $order[ 0 ][ 'column' ] > -1 ) { 
      $orderByColumns = [ 0 => 'id', 1 => 'agent_id', 2 => 'unique_upd_id', 4 => 'name', 6 => 'first_name', 8 => 'date_added' ];
      $orderBy  = isset( $orderByColumns[ $order[ 0 ][ 'column' ] ] ) ? $orderByColumns[ $order[ 0 ][ 'column' ] ] : 'id';
      $order    = $order[ 0 ][ 'dir' ] ? $order[ 0 ][ 'dir' ] : 'DESC';

      $filterCollection->setOrder( $orderBy, $order );
    }

    $filterCollection->setPageSize( $pageSize );
    $filterCollection->setCurPage( round( $page/$pageSize ) + 1 );

    return $filterCollection;

  }

}
