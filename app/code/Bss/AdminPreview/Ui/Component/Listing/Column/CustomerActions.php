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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;

/**
 * Class CustomerActions
 */
class CustomerActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * CustomerActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param Data $dataHelper
     * @param array $components
     * @param array $data
     */
    // @codingStandardsIgnoreStart
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
        $this->dataHelper = $dataHelper;

        if (!$this->dataHelper->isEnable() || $this->dataHelper->getCustomerGridLoginColumn() == 'actions' ||
            !$this->authorization->isAllowed('Bss_AdminPreview::login_button')) {
            unset($data);
            $data = [];
        }

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem($item)
    {
        $url = $this->urlBuilder->getUrl('adminpreview/customer/login', ['customer_id' => $item['entity_id']]);
        return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $url . '&quot;)">' . 'Login' . '</a>';
    }

}
