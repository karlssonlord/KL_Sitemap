<?php
/**
 * Sitemap
 * Copyright (C) 2014 Karlsson & Lord AB
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category  KL
 * @package   Sitemap
 * @author    Tykhon Dziuban <tykhon@karlssonlord.com>
 * @copyright 2014 Karlsson & Lord AB
 * @license   LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_Sitemap_Model_Resource_Catalog_Product extends Mage_Sitemap_Model_Resource_Catalog_Product
{
    /**
     * Get product collection array
     *
     * @param int $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        /* @var $store Mage_Core_Model_Store */
        $store = Mage::app()->getStore($storeId);
        if (!$store) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'main_table.entity_id = w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId());

        $storeId = (int)$store->getId();

        /** @var $urlRewrite Mage_Catalog_Helper_Product_Url_Rewrite_Interface */
        $urlRewrite = $this->_factory->getProductUrlRewriteHelper();
        $urlRewrite->joinTableToSelect($this->_select, $storeId);

        $this->_addFilter($storeId, 'visibility',
            Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in'
        );
        $this->_addFilter($storeId, 'status',
            Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in'
        );

        $this->_select->columns('updated_at');

        return $this->_loadEntities();
    }

    /**
     * Prepare catalog object
     *
     * @param array $row
     * @return Varien_Object
     */
    protected function _prepareObject(array $row)
    {
        $entity = new Varien_Object();
        $entity->setId($row[$this->getIdFieldName()]);
        $entity->setUrl($this->_getEntityUrl($row, $entity));
        $entity->setUpdatedAt($row['updated_at']);
        return $entity;
    }
}
