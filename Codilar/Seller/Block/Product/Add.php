<?php
/**
 * Created by PhpStorm.
 * User: manish
 * Date: 27/8/18
 * Time: 1:07 PM
 */

namespace Codilar\Seller\Block\Product;
use Magento\Catalog\Model\Category;
use \Magento\Catalog\Model\Indexer\Category\Flat\State ;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Add extends \Magento\Framework\View\Element\Template
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
    protected $_priceCurrency;
    protected $_imageBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $_imageBuilder,
        Category $category,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_categoryHelper = $categoryHelper;
        $this->request = $request;
        $this->_product = $productFactory;
        $this->_imageBuilder=$_imageBuilder;
        $this->_productRepository = $productRepository;
        $this->_priceCurrency = $priceCurrency;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_objectManager = $objectManager;
        $this->formKey = $context->getFormKey();
        $this->_stockItemRepository = $stockItemRepository;
        $this->_category = $category;

    }

    /**
     * Get form action URL for POST booking request
     *
     * @return string
     *
     */
    public function getProductData()
    {
        // use
        //$this->request->getParams(); // all params
        $id= $this->request->getParam('param');
        $product = $this->_product->create()->load($id);
        return $product;

    }

    public function getFormAction()
    {
        return $this->getUrl ('seller/product/save');

    }

//    public function getStockItem()
//    {
//        $id= $this->request->getParam('param');
//        return $this->_stockItemRepository->get($id);
//    }
    public function getStoreCategories()
    {
        return $this->_categoryHelper->getStoreCategories();
    }




    public function getChildCategories($categoryId)
    {

        $_category = $this->_categoryFactory->create();

        $category = $_category->load($categoryId);

        //Get category collection
        $collection = $category->getCollection()
            ->addIsActiveFilter()
            ->addOrderField('name')
            ->addIdFilter($category->getChildren());
        return $collection;
    }

    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }


}