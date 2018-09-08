<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 30/8/18
 * Time: 11:00 PM
 */

namespace Codilar\Seller\Observer;
use Magento\Framework\Event\ObserverInterface;




class SalesOrderSaveAfter implements ObserverInterface {

    protected $_commission;
    protected $_customerSession;
    protected $_product;
    protected $orderRepository;
    protected $_urlRewriteFactory;
    protected $_sellerProfileFactory;

    public function __construct(
        \Codilar\Seller\Model\CommissionFactory $commissionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Codilar\Seller\Model\SellerProfileFactory $sellerProfileFactory,
        UrlRewriteFactory $urlRewriteFactory

    )
    {
        $this->_commission=$commissionFactory;
        $this->orderRepository = $orderRepository;
        $this->_customerSession = $customerSession;
        $this->_product = $productFactory;
        $this->_urlRewriteFactory=$urlRewriteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        if($order->getState() == 'complete') {

            $order=$this->orderRepository->get($order->getId());
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test1.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($order->getState());
            $logger->info($order->getId());
            $logger->info($order->getIncrementId());
            foreach ($order->getAllItems() as $item) {

                $pid=$item->getProductId();
                $logger->info($pid);

            }

            $product =  $this->_product->create()->load($pid);
            $vendorId=$product->getVendorId();
            $logger->info("vendorId".$vendorId);
            $commission=$this->_commission->create()->getCollection()
                ->addFieldToFilter('seller_id', $vendorId);


            //get commission amount
            $commissionRate=$commission->getFirstItem()->getCommissionRate();

            $logger->info($commission->getFirstItem()->getCommissionRate().$order->getGrandTotal());
            //$commission->setData($commission->getCommissionRate()+$order->getGrandTotal());
            //$commission->setData($commission->get()+$order->getGrandTotal());

            //getting old sale amount
            $totalAmount=$commission->getFirstItem()->getTotalSale();
            $newAmount=$totalAmount+$order->getGrandTotal();
            $logger->info("new amount".$newAmount);
            // getting old commission amount
            $commissionAmount=$commission->getFirstItem()->getCommissionAmount();

            $newCommissionAmount=$order->getGrandTotal()*($commissionRate/100);

            $updatecommissionAmount=$newCommissionAmount+$commissionAmount;
            $logger->info("update commission".$updatecommissionAmount);

            //get commssion recieved amount
            $commissionrecieved=$commission->getFirstItem()->getReceivedAmount();
            $commissionAmountpaid=$commission->getFirstItem()->getAmountPaid();
            $newCommissionAmountPaid=$commissionrecieved-$commissionAmountpaid;

            // setting new data to commission table
            $commission->getFirstItem()->setTotalSale($newAmount);
            $commission->getFirstItem()->setCommissionAmount($updatecommissionAmount);
            $commission->getFirstItem()->setAmountPaid ($newCommissionAmountPaid);
            if( $commission->save ())
            {
                $logger->info("update commission success");
            }
            else{
                $logger->info("not update commission success");
            }



        }

    }
}