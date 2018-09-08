<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 4/9/18
 * Time: 8:32 PM
 */

namespace Codilar\CronWorld\Cron;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Psr\Log\LoggerInterface;


class CustomClassName
{

    protected $logger;
    protected $_orderFactory;
    protected $_orderCollection;
    public function __construct(
        LoggerInterface $logger,
    Order $orderFactory,
        CollectionFactory $order


) {
        $this->logger = $logger;
        $this->_orderCollection=$order;
        $this->_orderFactory=$orderFactory;

    }



    public function execute() {
        $this->logger->info('Cron Works----------------------------------------------------------------------');
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info( 'hit me');
        $collection=$this->_orderCollection->create()->addFieldToFilter ('status','pending');
        foreach ($collection as $order) {
            foreach ($order->getAllItems() as $item)
            {

                $Id=$item->getOrderId();
                $logger->info( $Id);
                $model=$this->_orderFactory->load($Id);
                $logger->info( $Id);
                if(!$model->canCancel())
                {
                    $this->logger->info('Can not cancel');
                }
                else{
                    $model->cancel();
                }
                $model->setStatus('cancel pending order');
                $model->save();


            }
        }






    }
}