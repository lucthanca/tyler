<?php
declare(strict_types=1);
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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Ui\Component\DataProvider;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Ui\Component\DataProvider\Document as BePlugged;

/**
 * Class Document
 * ^ca_.+
 */
class Document
{
    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * Document constructor.
     *
     * @param CustomerMetadataInterface $customerMetadata
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadata
    ) {
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Set is customer attribute value
     *
     * @param BePlugged $subject
     * @param callable $proceed
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface
     */
    public function aroundGetCustomAttribute(
        BePlugged $subject,
        callable $proceed,
        $attributeCode
    ) {
        if (preg_match("/^ca_.+/", $attributeCode)) {
            $this->setBssCustomerAttributeValue($subject, $attributeCode);
        }

        return $proceed($attributeCode);
    }

    /**
     * Set customer attribute label instead value
     *
     * @param BePlugged $subject
     * @param string $attributeCode
     */
    protected function setBssCustomerAttributeValue(BePlugged $subject, string $attributeCode)
    {
        $value = $subject->getData($attributeCode);

        if (!$value) {
            $subject->setCustomAttribute($attributeCode, null);
            return;
        }


        try {
            $attributeMetadata = $this->customerMetadata->getAttributeMetadata($attributeCode);

            foreach ($attributeMetadata->getOptions() as $option) {
                if ($option->getValue() == $value) {
                    $attributeOption = $option;
                }
            }
            if (!isset($attributeOption)) {
                $subject->setCustomAttribute($attributeCode, $value);
                return;
            }

            $subject->setCustomAttribute($attributeCode, $attributeOption->getLabel());
        } catch (\Exception $e) {
            $subject->setCustomAttribute($attributeCode, null);
        }
    }
}
