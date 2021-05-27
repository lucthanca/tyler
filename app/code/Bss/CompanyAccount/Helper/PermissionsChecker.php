<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Model\SubUser;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

/**
 * Class PermissionsChecker
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PermissionsChecker
{
    /**
     * @var CustomerSessionFactory
     */
    protected $customerSessionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $userOrderRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * DownloadableProducts constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $helper
     * @param RedirectFactory $redirectFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubRoleRepositoryInterface $roleRepository
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     */
    public function __construct(
        CustomerSessionFactory $customerSessionFactory,
        \Psr\Log\LoggerInterface $logger,
        Data $helper,
        RedirectFactory $redirectFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        SubUserRepositoryInterface $subUserRepository,
        SubRoleRepositoryInterface $roleRepository,
        SubUserOrderRepositoryInterface $userOrderRepository
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManager = $this->helper->getStoreManager();
        $this->customerSession = $this->helper->getCustomerSession();
        $this->roleRepository = $roleRepository;
        $this->redirectFactory = $redirectFactory;
        $this->subUserRepository = $subUserRepository;
        $this->userOrderRepository = $userOrderRepository;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Check permissions by permissions value
     *
     * @param int $permissionValue
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function check($permissionValue)
    {
        if ($this->isDenied($permissionValue)) {
           return $this->redirectPermissionPage();
        }
        return false;
    }

    /**
     * Check permission
     *
     * @param int $permissionValue
     * @param int|null $value
     *
     * @return bool|array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isDenied($permissionValue, $value = null)
    {
        /** @var SubUser $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($this->helper->isEnable($this->storeManager->getWebsite()->getId()) && $subUser) {
            $subUser = $this->subUserRepository->getById($subUser->getSubUserId());
            $assignedRole = $this->roleRepository->getById($subUser->getRelatedRoleId());
            if (!$subUser->canAccess(Permissions::ADMIN, $assignedRole)) {
                if ($permissionValue == Permissions::MAX_ORDER_AMOUNT) {
                    if ($assignedRole->getMaxOrderAmount() == null) {
                        return ['is_denied' => false];
                    }
                    return [
                        'is_denied' => (float) $assignedRole->getMaxOrderAmount() < (float) $value,
                        'accessible_value' => $this->getFormatCurrency($assignedRole->getMaxOrderAmount(), true)
                    ];
                }
                if ($permissionValue == Permissions::MAX_ORDER_PERDAY) {
                    if ($assignedRole->getMaxOrderPerDay() == null) {
                        return ['is_denied' => false];
                    }
                    $maxOrderPerDay = (int) $assignedRole->getMaxOrderPerDay();
                    $curDayOrderNum = (int) $this->getSubUserOrderCount($subUser->getSubUserId());
                    return [
                        'is_denied' => $maxOrderPerDay <= $curDayOrderNum,
                        'accessible_value' => $assignedRole->getMaxOrderPerDay()
                    ];
                }
                return !$subUser->canAccess($permissionValue, $assignedRole);
            }
        }
        return false;
    }

    /**
     * Get format currency value
     *
     * @param float $value
     * @param bool $isFormat
     * @return float|string
     */
    protected function getFormatCurrency($value, $isFormat = false)
    {
        return $this->pricingHelper->currency($value, $isFormat, false);
    }

    /**
     * Get message manager object
     *
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager()
    {
        return $this->helper->getMessageManager();
    }

    /**
     * Get sub-order by customer ID
     *
     * @param int $subUserId
     * @return int
     */
    protected function getSubUserOrderCount($subUserId)
    {
        $date = $this->helper->getDateTimeFactory()->create();
        $searchCriteriaBuilder = $this->helper->getDataHelper()->getSearchCriteriaBuilder();
        $filterDateFrom = $date->format('Y-m-d') . ' 00:00:00';
        $filterDateTo = $date->format('Y-m-d') . ' 23:59:59';
        $searchCriteriaBuilder->addFilter('created_at', $filterDateFrom, 'gteq');
        $searchCriteriaBuilder->addFilter('created_at', $filterDateTo, 'lteq');
        $searchCriteriaBuilder->addFilter('sub_id', $subUserId);
        $searchCriteria = $searchCriteriaBuilder->create();
        $subOrders = $this->userOrderRepository->getList($searchCriteria);
        return $subOrders->getTotalCount();
    }

    /**
     * Allow quote with sub-user Company Account
     *
     * @param string $permissionValue
     * @return bool
     * @throws NoSuchEntityException
     */
    public function allowQuote($permissionValue)
    {
        try {
            if (!$this->helper->isEnable($this->storeManager->getWebsite()->getId())) {
                return true;
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            return true;
        }
        /** @var SubUser $subUser */
        $subUser = $this->customerSessionFactory->create()->getSubUser();
        if($subUser == null) {
            return true;
        }
        $subUser = $this->subUserRepository->getById($subUser->getSubUserId());
        $assignedRole = $this->roleRepository->getById($subUser->getRelatedRoleId());
        if ($assignedRole->getRoleType() == Permissions::ADMIN) {
            return true;
        }
        return $subUser->canAccess($permissionValue, $assignedRole);
    }

    /**
     * Check view quotes with sub-userCompany Account
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NoSuchEntityException
     */
    public function redirectPermissionPage(){
        $this->helper->getMessageManager()->addErrorMessage(
            __('You have no permission to access that page.')
        );
        return $this->redirectFactory->create()
            ->setpath('');
    }

    /**
     * Get Sub-User Id
     *
     * @return boolean|mixed
     * @throws LocalizedException
     */
    public function getSubUserId(){
        if($this->helper->isEnable($this->storeManager->getWebsite()->getId())){
            /** @var SubUser $subUser */
            $subUser = $this->customerSessionFactory->create()->getSubUser();
            if($subUser == null) {
                return false;
            }
            return $subUser->getSubUserId();
        }
        return false;
    }
}
