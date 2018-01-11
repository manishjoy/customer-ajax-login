<?php
namespace ManishJoy\CustomerLogin\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory->create();
        parent::__construct($context,$registry);
    }


    public function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getWebsiteId();
    }

    public function userExists($email)
    {
        $customer = $this->customerFactory->setWebsiteId($this->getWebsiteId());
        if ($this->customerFactory->loadByEmail($email)->getId()) {
            return true;
        }  else {
            return false;
        }
    }

    public function createUser($userData)
    {
        try {
            $customer = $this->customerFactory->setWebsiteId($this->getWebsiteId());
            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['firstname']);
            $customer->setLastname($userData['lastname']);
            $customer->setPassword($userData['password']);
            if ($customer->save()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
