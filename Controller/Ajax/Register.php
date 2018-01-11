<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ManishJoy\CustomerLogin\Controller\Ajax;

use Magento\Framework\Exception\LocalizedException;

/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Register extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \ManishJoy\CustomerLogin\Model\Customer $customerModel
     */
    protected $customerModel;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize Login controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \ManishJoy\CustomerLogin\Model\Customer $customerModel,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerModel = $customerModel;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    public function execute()
    {
        $userData = null;
        $httpBadRequestCode = 400;
        $response = [
            'errors' => false,
            'message' => __('Registration successful.')
        ];

        if ($this->customerModel->userExists($this->getRequest()->getPost('email'))) {
            $response = [
                'errors' => true,
                'message' => __('A user already exists with this email id.')
            ];
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            try {
                $userData = [
                                    'firstname' => $this->getRequest()->getPost('firstname'),
                                    'lastname' => $this->getRequest()->getPost('lastname'),
                                    'email' => $this->getRequest()->getPost('email'),
                                    'password' => $this->getRequest()->getPost('password'),
                                    'password_confirmation' => $this->getRequest()->getPost('password_confirmation')
                                ];
            } catch (\Exception $e) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            if (!$userData || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            try {
                $isUserRegistered = $this->customerModel->createUser($userData);
                if (!$isUserRegistered) {
                    $response = [
                        'errors' => true,
                        'message' => __('Something went wrong.')
                    ];
                } else {
                    $customer = $this->customerAccountManagement->authenticate(
                        $userData['email'],
                        $userData['password']
                    );
                    $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    $this->customerSession->regenerateId();
                }
            } catch (LocalizedException $e) {
                $response = [
                    'errors' => true,
                    'message' => $e->getMessage()
                ];
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __('Something went wrong.')
                ];
            }
        }

            
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
