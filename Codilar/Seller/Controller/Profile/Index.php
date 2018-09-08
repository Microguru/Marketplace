<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 29/8/18
 * Time: 2:28 AM
 */

namespace Codilar\Seller\Controller\Profile;


use Codilar\Seller\Model\SellerProfileFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Index  extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_sellerProfileFactory;
    protected $_fileUploaderFactory;
    protected $_filesystem;
    protected $_directoryList;
    protected $_customerFactory;
    protected $_customerCollectionFactory;
    protected $_response;
    protected $_customer;
    protected $_customerRepository;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $pageFactory,
        CustomerFactory $customerFactory,
        Collection $customerCollection,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        SellerProfileFactory $profileFactory,
        Http $responseHttp,
        DirectoryList $directoryList,
        UploaderFactory $fileUploaderFactory,
        Filesystem $fileSystem)
    {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_directoryList = $directoryList;
        $this->_pageFactory = $pageFactory;
        $this->_customerCollectionFactory=$customerCollection;
        $this->_customerFactory=$customerFactory;
        $this->_response=$responseHttp;
        $this->_customer=$customerResourceFactory;
        $this->_customerRepository=$customerRepository;
        $this->_filesystem = $fileSystem;
        $this->_sellerProfileFactory =$profileFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        if(!empty($_POST)) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test1.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $this->_customerFactory->create();


            $logger->info("enter");
            $sellerid = $_POST["seller_id"];
            $logger->info($sellerid);
            $pro=$this->_customerFactory->create()->load($sellerid,'id');



            $sellername = $_POST["seller_name"];
            $shopUrl=$_POST["shop_url"];
            $logger->info ( $pro->getData('shop_url') );
            if($pro->getData('shop_url')!=$shopUrl )
            {
                $logger->info ( $shopUrl );
               // $collection=$this->__customerCollectionFactory->addAttributeToSelect ('id')->addAttributeToFilter('shop_url', array('eq' => $shopUrl));

                $collection=$this->_customerCollectionFactory->addAttributeToSelect('*')->addAttributeToFilter('shop_url', array('eq' => $shopUrl));
                    $logger->info ( $collection->getData('id') );

                if(!empty($collection->getData( 'id' )))
                {
                    $this->messageManager->addErrorMessage(__('Your Shop_Url is already in used Please try again'));
                    $this->_response->setRedirect('seller/profile/index');

                }
                else{
                    $customer = $this->_customerRepository->getById($sellerid);
                    $customer->setCustomAttribute('shop_url',$shopUrl);
                    $this->_customerRepository->save($customer);

                }
            }

            $aboutUs = $_POST['about_us'];
            $paymentInformation = $_POST['payment_information'];
            $returnPolicy = $_POST['return_policy'];
            $shippingPolicy = $_POST['shipping_policy'];
            $country = $_POST['country'];
            $profile = $this->_sellerProfileFactory->create()->load($sellerid,'seller_id' );
            $profile->setData( 'seller_id', $sellerid );
            $profile->setData( 'seller_name', $sellername );
            $profile->setData ( 'shop_url', $shopUrl );
            if(isset($_FILES['shop_image'])) {
                if ($_FILES['shop_image']['name']) {
                    try {

                        $uploader =$this->_fileUploaderFactory->create(['fileId' => 'shop_image']);


                        $uploader->setAllowedExtensions ( ['jpg', 'jpeg', 'gif', 'png'] );
                        // $uploader->setAllowRenameFiles (true);
                        $uploader->setFilesDispersion ( false );
                        // get media directory
                        $mediaDirectory = $this->_filesystem->getDirectoryRead( DirectoryList::MEDIA );
                        $uploader->save ( $mediaDirectory->getAbsolutePath( "/images/" ) );
                    } catch (Exception $e) {
                        \Zend_Debug::dump ( $e->getMessage () );
                    }
                }
                $imagepath=$_FILES['shop_image']['name'];
                $imagePath ="/images/".$imagepath;
                //echo $imagePath;
                $profile->setData('shop_image',$imagePath);
            }


            $profile->setData ( 'payment_information', $paymentInformation );
            $profile->setData ( 'about_us', $aboutUs );
            $profile->setData ( 'return_policy', $returnPolicy );
            $profile->setData ( 'shipping_policy', $shippingPolicy );
            $profile->setData ( 'country', $country );
            $profile->save ();
        }


        return $this->_pageFactory->create();
    }
}