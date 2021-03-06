<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 3/9/18
 * Time: 10:27 AM
 */

namespace Codilar\Seller\Block\Product;


 use Magento\Catalog\Model\Category;
use \Magento\Catalog\Model\Indexer\Category\Flat\State ;
 use Magento\CatalogInventory\Api\StockStateInterface;
 use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */

    protected $formKey;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $_productFactory;
    protected $request;
    protected $_product;
    protected $_stockItemRepository;
    protected $_imageBuilder;
    protected $_category;
    protected $_priceCurrency;
    protected $_stockQuantity;

    public function __construct (
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Category $category,
        StockStateInterface $stockState,
        \Magento\Catalog\Block\Product\ImageBuilder $_imageBuilder,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct ( $context, $data );
        $this->_categoryHelper = $categoryHelper;
        $this->request = $request;
        $this->_product = $productFactory;
        $this->_productRepository = $productRepository;
        $this->_priceCurrency = $priceCurrency;
        $this->_stockQuantity=$stockState;
        $this->_imageBuilder=$_imageBuilder;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_objectManager = $objectManager;
        $this->formKey = $context->getFormKey ();
        $this->_stockItemRepository = $stockItemRepository;
        $this->_category = $category;


    }

    /**
     * Get form action URL for POST booking request
     *
     * @return string
     *
     */
    public function getProductData ()
    {
        // use
        //$this->request->getParams(); // all params
        $id = $this->request->getParam ( 'param' );
        $product = $this->_product->create ()->load ( $id );
        return $product;

    }

    public function getProductId ()
    {
        $id = $this->request->getParam ( 'param' );
        return $id;
    }

    public function getLoadProduct($id)
    {
        return $this->_product->create()->load($id);
    }


    public function getImagePath()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $imagePath = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $imagePath ;
    }

    public function getFormAction ()
    {
        return $this->getUrl ( 'seller/product/edit' );

    }


     public function getStoreCategories ()
     {
         return $this->_categoryHelper->getStoreCategories ();
     }


    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }


    public function getChildCategories ($categoryId)
     {

         $_category = $this->_categoryFactory->create ();

         $category = $_category->load ( $categoryId );

         //Getpublic function getCurrentCurrencySymbol()
         //{
         //  return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
         //} category collection
         $collection = $category->getCollection ()
             ->addIsActiveFilter ()
             ->addOrderField ( 'name' )
             ->addIdFilter ( $category->getChildren () );
         return $collection;
     }
     public function getCategoryName()
     {
         $id = $this->request->getParam ( 'param' );
         $product=$this->_product->create()->load($id);
         $categories = $product->getCategoryIds();
         $cat_name=array();
         foreach($categories as $category)
         {
             $cat =$this->_category->load($category);
             $cat_name[] =  $cat->getName();
         }
         return $cat_name;
     }

    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }

    public function getStockItem()
    {
        $id = $this->request->getParam( 'param' );
        $stockquantity=$this->_stockQuantity->getStockQty($id);
        return $stockquantity;
    }


 }

