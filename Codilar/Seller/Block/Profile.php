<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 29/8/18
 * Time: 10:44 AM
 */

namespace Codilar\Seller\Block;
use \Codilar\CustomLogin\Helper\Vendor;
use \Codilar\Seller\Model\SellerProfile;


class Profile  extends \Magento\Framework\View\Element\Template
{
    protected $_helperData;
    protected $_sellerProfile;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Vendor $helperData,
        SellerProfile $profileFactory,

        array $data = [])
    {
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
        $this->_sellerProfile=$profileFactory;

    }



    public function getFormAction()
    {
        return $this->getUrl ('seller/Profile/index');

    }


    public function getCustomerId()
    {
        return $this->_helperData->getCustomerId();
    }

    public function getProfileData()
    {   $id=$this->_helperData->getCustomerId();
        $profile=$this->_sellerProfile->load($id,'seller_id');
        return $profile;
    }


    public function getImagePath()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $imagePath = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $imagePath ;
    }
}