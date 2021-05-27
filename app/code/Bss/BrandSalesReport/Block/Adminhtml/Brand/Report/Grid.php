<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_BrandSalesReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandSalesReport\Block\Adminhtml\Brand\Report;

use Bss\BrandRepresentative\Helper\Data;
use Bss\BrandSalesReport\Model\ResourceModel\Report\BrandSalesReport\Collection;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;
use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;

/**
 * Class Grid
 * Bss\BrandSalesReport\Block\Adminhtml\Sales\Report
 */
class Grid extends AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param SerializerInterface $serializer
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        SerializerInterface $serializer,
        Data $helper,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $resourceFactory, $collectionFactory, $reportsData, $data);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getResourceCollectionName()
    {
        return Collection::class;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            [
                'header' => __('Period'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order ID'),
                'index' => 'order_id',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-order-id',
                'column_css_class' => 'col-order-id'
            ]
        );
        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'product_sku',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );
        $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );
        $this->addColumn(
            'product_type',
            [
                'header' => __('Product Type'),
                'index' => 'product_type',
                'type' => 'options',
                'options' => $this->helper->getAllProductTypes(),
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );
        $this->addColumn(
            'product_brand',
            [
                'header' => __('Brand'),
                'index' => 'brand_name',
                'type' => 'string',
                'sortable' => true,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand'
            ]
        );
        $this->addColumn(
            'product_children',
            [
                'header' => __("Product children"),
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'type' => 'string',
                'frame_callback' => [$this, "processChildrenProduct"]
            ]
        );
        $this->addColumn(
            'company_name',
            [
                'header' => __("Company Name"),
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'type' => 'string',
                'index' => 'company_name'
            ]
        );
        $this->addAddressCol();
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $this->addColumn(
            'qty_ordered',
            [
                'header' => __('Order Quantity'),
                'index' => 'qty_ordered',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Add address columns
     */
    protected function addAddressCol()
    {
        $this->addColumn(
            'address',
            [
                'header' => __("Address"),
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'type' => 'string',
                'index' => 'address'
            ]
        );
        $this->addColumn(
            'city',
            [
                'header' => __("City"),
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'type' => 'string',
                'index' => 'city'
            ]
        );
        $this->addColumn(
            'province',
            [
                'header' => __("Province"),
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'type' => 'string',
                'index' => 'province'
            ]
        );
    }

    /**
     * Render children items columns
     *
     * @param string $value - html text
     * @param \Magento\Reports\Model\Item $reportItem
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function processChildrenProduct($value, $reportItem, $column): string
    {
        if (!isset($reportItem['product_options']) || !$reportItem['product_options']) {
            return "";
        }
        try {
            $rawItems = $this->serializer->unserialize($reportItem['product_options']);
        } catch (\Exception $e) {
            $this->_logger->critical(
                __("Error when unserialize child product on brand sales report: ") .
                $e
            );
            $rawItems = [];
        }
        return $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class
        )->setTemplate("Bss_BrandSalesReport::brand/report/grid/children-item.phtml")
            ->assign("rawItems", $rawItems)->_toHtml();
    }

    /**
     * Merge array
     *
     * @param array $arr1
     * @param array $needle
     */
    private function mergeArray(array &$arr1, array $needle)
    {
        $arr1 = array_merge($arr1, $needle);
    }

    /**
     * Get brand filter
     *
     * @return array
     */
    protected function getBrandFilter(): array
    {
        $filterData = $this->getFilterData();
        $brandFilter=[];

        if ($filterData->getData('brands')) {
            $brands = $filterData->getData('brands');
            foreach ($brands as $brand) {
                if (!is_array($brand)) {
                    $brand = explode(",", $brand);
                }
                $this->mergeArray($brandFilter, $brand);
            }
        }

        return $brandFilter;
    }

    /**
     * Add brand filter
     *
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection $collection
     * @param \Magento\Framework\DataObject $filterData
     * @return Grid
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        $brands = $filterData->getData('brands');
        if (isset($brands[0])) {
            $brandIds = explode(',', $brands[0]);
            array_walk($brandIds, function (&$id) {
                $id = (int) $id;
            });
            $collection->setBrandFilter($brandIds);
        }

        return parent::_addCustomFilter($collection, $filterData);
    }
}
