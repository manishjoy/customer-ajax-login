<?php
namespace ManishJoy\CustomerLogin\Block;
  
class Popup extends \Magento\Framework\View\Element\Template
{   
	protected $_session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
    	$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        parent::__construct($context);
    }

    public function isLoggedIn()
    {
    	return $this->_customerSession->isLoggedIn();
    }
}