<?php
namespace Smartwave\NewFingerprints\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;

class Cron extends \Magento\Framework\App\Action\Action {

    protected $_helper,
              $resourceConnection;

    public function __construct(
            Context $context,
            Session $customerSession,
            ResourceConnection $resourceConnection
        ) {

        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }	

    public function execute() {
        // commented for stop services of archive  
        // $this->extractArchives( );
        die( 'Done' );
    }
    
    private  function extractArchives(  ) {
        $connection  = $this->resourceConnection->getConnection();

        $detailTable = $connection->getTableName('customer_detail');
        $detailArchiveTable = $connection->getTableName('customer_detail_archives');
        $imageTable = $connection->getTableName('customer_images');
        $imageArchiveTable = $connection->getTableName('customer_image_archives');

        $query = "SELECT * FROM `$imageTable` WHERE `cust_id` IN ( SELECT id FROM `$detailTable` WHERE `date_added` < DATE_SUB( NOW( ), INTERVAL 90 DAY ) );";
        $records = $connection->fetchAll($query);
        echo '<pre>';
        if( $records ) { 
            $path = $_SERVER['DOCUMENT_ROOT'].'/api/customer_images/';
            $thumbPath = $_SERVER['DOCUMENT_ROOT'].'/api/thumbs/';
            
            $archivePath = $_SERVER['DOCUMENT_ROOT'].'/api/archive/customer_images/';
            $archiveThumbPath = $_SERVER['DOCUMENT_ROOT'].'/api/archive/thumbs/';
            if( !file_exists( $archivePath ) ) mkdir( $archivePath, 0777, true );
            if( !file_exists( $archiveThumbPath ) ) mkdir( $archiveThumbPath, 0777, true );

            foreach( $records as $record ) { 
                if( !$record[ 'image_name' ] ) { 
                    continue;
                }

                echo $record[ 'image_name' ] . ': ';

                if( file_exists( $path . $record[ 'image_name' ] ) ) { 
                    if( copy( $path . $record[ 'image_name' ], $archivePath . $record[ 'image_name' ] ) ) { 
                        echo 'Main image copied';
                        if( unlink( $path . $record[ 'image_name' ] ) ){ 
                            echo ' & deleted.';
                        } else { 
                            echo ' but not deleted.';
                        }
                    } else { 
                        echo 'Main image not copied.';
                    }
                }

                if( file_exists( $thumbPath . $record[ 'image_name' ] ) ) { 
                    if( copy( $thumbPath . $record[ 'image_name' ], $archiveThumbPath . $record[ 'image_name' ] ) ) { 
                        echo 'Thumb image copied';
                        if( unlink( $thumbPath . $record[ 'image_name' ] ) ) { 
                            echo ' & deleted.';
                        } else { 
                            echo ' but not deleted.';
                        }
                    } else { 
                        echo 'Thumb image not copied.';
                    }
                }
                echo '<br/>';
            }
        }
        
        $query = "INSERT INTO `$detailArchiveTable` SELECT * FROM `$detailTable` WHERE `date_added` < DATE_SUB( NOW( ), INTERVAL 90 DAY );";
        $connection->query($query);
        
        $query = "INSERT INTO `$imageArchiveTable` SELECT * FROM `$imageTable` WHERE `cust_id` IN ( SELECT id FROM `$detailTable` WHERE `date_added` < DATE_SUB( NOW( ), INTERVAL 90 DAY ) );";
        $connection->query($query);

        $query = "DELETE FROM `$imageTable` WHERE `cust_id` IN ( SELECT id FROM `$detailTable` WHERE `date_added` < DATE_SUB( NOW( ), INTERVAL 90 DAY ) );";
        $connection->query($query);

        $query = "DELETE FROM `$detailTable` WHERE `date_added` < DATE_SUB( NOW( ), INTERVAL 90 DAY );";
        $connection->query($query);

        die( 'Success' );
    }
	
}