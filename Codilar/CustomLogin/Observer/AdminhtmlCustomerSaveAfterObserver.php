<?php


namespace Codilar\CustomLogin\Observer;

use Codilar\Seller\Model\SellerProfileFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

use \Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Customer\Api\AddressRepositoryInterface;
use Codilar\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Codilar Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{

    protected $_customerCollectionFactory;
    protected $_sellerCollectionFactory;
    protected $_objectManager;
    protected $customerRepositoryInterface;
    protected $customerAddressRepositoryInterface;
    protected $logger;
    protected $_storeManager;
    protected $_seller;
    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $_sellerProfileFactory;
    protected $_urlRewriteFactory;
    public function __construct (
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Codilar\Seller\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codilar\Seller\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        UrlRewriteFactory $urlRewriteFactory,
        SellerProfileFactory $sellerProfileFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Translate\Inline\StateInterface $state,
        \Magento\Customer\Api\AddressRepositoryInterface $customerAddressRepositoryInterface,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->_objectManager = $objectManager;
        $this->_seller=$sellerFactory;
        $this->_urlRewriteFactory=$urlRewriteFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_storeManager=$storeManager;
        $this->_transportBuilder=$transportBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerAddressRepositoryInterface = $customerAddressRepositoryInterface;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->_date = $date;
        $this->_sellerProfileFactory=$sellerProfileFactory;
        $this->inlineTranslation=$state;
        $this->logger = $loggerInterface;
    }


    public function execute (\Magento\Framework\Event\Observer $observer)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);



        $customer = $observer->getCustomer ();
        $this->logger->debug($customer->getFirstName());
        $customerId=(int)$customer->getId();
        $customerEmail=$customer->getEmail();
        $customerName=$customer->getFirstName();


        $logger->info('before execute customer obserer111');
        $model = $this->_seller->create();
        $isVendor=$customer->getCustomAttribute('is_vendor')->getValue();
        $isApprovedAccount = $customer->getCustomAttribute('approve_account')->getValue();
        $shopurl=$customer->getCustomAttribute('shop_url')->getValue();
        $logger->info('obserer shop_url');
        if($isApprovedAccount && $isVendor)
        {
            $model->setData('seller_id',$customerId);
            $model->setData('seller_email',$customerEmail);
            $model->setData('seller_name',$customerName);
            $model->setData('shop_url',$shopurl);
//            $customer = $this->customerRepositoryInterface->getById($customerId);
//            $billingAddressId = $customer->getDefaultBilling();
//            $billingAddress = $this->customerAddressRepositoryInterface->getById($billingAddressId);
//            $telephone = $billingAddress->getTelephone();
//            if($telephone)
//            {
//                $model->setData ('phone', $telephone);
//            }
            $model->setData('seller_status',$isApprovedAccount);
            $model->setData('created_at',$this->_date->gmtDate());
            $logger->info('before execute obserer111');
            $model->save();
            $model = $this->_objectManager->create(
                'Codilar\Seller\Model\Commission');


            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND

            , 'store' => $this->_storeManager->getStore()->getId());
            $templateVars = array(
                'customer_name' => $customerName,
                'message'   => 'Hello World!!.'
            );
            $from = array('email' => "ravisharma73726@gmail.com", 'name' => 'Manish Kumar');
            $this->inlineTranslation->suspend();
            $to = array($customerEmail);
            $transport = $this->_transportBuilder->setTemplateIdentifier('vendor_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();




            $model->setData('seller_id',$customerId);
            $model->setData('seller_name',$customerName);
            $model->setData('commission_rate',0);
            $model->setData('total_sale',0);
            $model->setData('received_amount',0);
            $model->setData('commission_amount',0);
            $model->setData('amount_paid',0);
            $model->save();
        }

        $logger->info('profile setting');
            $profile=$this->_sellerProfileFactory->create();
            $profile->setData('seller_id',$customerId);
        $profile->setData('seller_name', $customerName);
            $profile->setData('shop_url',$shopurl);
            $profile->save ();
            $urlRewriteModel = $this->_urlRewriteFactory->create();
            /* set current store id */
            $urlRewriteModel->setStoreId(1);
            /* this url is not created by system so set as 0 */
            $urlRewriteModel->setIsSystem(0);
            /* unique identifier - set random unique value to id path */
            $urlRewriteModel->setIdPath(rand(1, 100000));
            /* set actual url path to target path field */
            $urlRewriteModel->setTargetPath("seller/homepage/profile/shop/".$shopurl);
            /* set requested path which you want to create */
            $urlRewriteModel->setRequestPath("homepage/".$shopurl);
            /* set current store id */
            $urlRewriteModel->save();

        $logger->info('done');







}


}