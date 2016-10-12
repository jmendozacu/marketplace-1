<?php

class Customs_Register_Model_Customer extends Mage_Customer_Model_Customer
{
    private static $_isConfirmationRequired;
    
    public function isConfirmationRequired()
    {

        if ($this->canSkipConfirmation()) {
            return false;
        }
        if (self::$_isConfirmationRequired === null) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : null;
            
            // sent email active to user group vendors : id=2
            if($this->getGroupId() == 2) {
                self::$_isConfirmationRequired = (bool)Mage::getStoreConfig(self::XML_PATH_IS_CONFIRM, $storeId);
            }
        }

        return self::$_isConfirmationRequired;
    }
    
    public function canSkipConfirmation()
    {
        return $this->getId() && $this->hasSkipConfirmationIfEmail()
            && strtolower($this->getSkipConfirmationIfEmail()) === strtolower($this->getEmail());
    }
    
}
?>