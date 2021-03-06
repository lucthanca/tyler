<?php
/**
 * Class for Restrictcustomergroup NewConditionHtml
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     */
    public function execute()
    {
      $id = $this->getRequest()->getParam('id');
      $formName = $this->getRequest()->getParam('form_namespace');
      $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
      $type = $typeArr[0];
      $model = $this->_objectManager->create($type)
              ->setId($id)
              ->setType($type)
              ->setRule($this->_objectManager->create('FME\Restrictcustomergroup\Model\Rule'))
              ->setPrefix('conditions');
      if (!empty($typeArr[1]))
      {
          $model->setAttribute($typeArr[1]);
      }
      if ($model instanceof AbstractCondition)
      {
          $model->setJsFormObject($this->getRequest()->getParam('form'));
          $model->setFormName($formName);
          $html = $model->asHtmlRecursive();
      }
      else
      {
          $html = '';
      }
      $this->getResponse()->setBody($html);
    }
}
