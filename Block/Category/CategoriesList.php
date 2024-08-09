<?php

declare(strict_types=1);

namespace EPuzzle\CategoriesWidget\Block\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Catalog Categories List widget block
 */
class CategoriesList extends Template implements BlockInterface, IdentityInterface
{
    /**
     * CategoriesList
     *
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [
                Category::CACHE_TAG,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [Category::CACHE_TAG];
    }

    /**
     * @inheritDoc
     */
    public function getCacheKeyInfo()
    {
        return [
            'CATALOG_CATEGORIES_LIST_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->getCategoryId(),
            $this->getTemplate(),
            $this->getTitle(),
        ];
    }

    /**
     * Get the widget title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getData('title') ?: null;
    }

    /**
     * Get the category ID
     *
     * @return int
     */
    public function getCategoryId(): int
    {
        if ($this->getData('category_id')) {
            return (int)$this->getData('category_id');
        }

        return (int)$this->getRequest()->getParam('id');
    }

    /**
     * Is Telegram web environment?
     *
     * @return bool
     */
    public function isTgEnv(): bool
    {
        if ($this->hasData('is_tg_env')) {
            return (bool)$this->getData('is_tg_env');
        }

        return true;
    }

    /**
     * Get the categories collection
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function getCategoriesCollection(): Collection
    {
        if (!$collection = $this->getData('categories_collection')) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect(['name', 'image_url', 'image', 'description']);
            $collection->addAttributeToFilter('parent_id', ['eq' => $this->getCategoryId()]);
            $collection->addIsActiveFilter();
            $collection->addNavigationMaxDepthFilter();
            $collection->addUrlRewriteToResult();
            $collection->addOrder('level', Collection::SORT_ORDER_ASC);
            $collection->addOrder('position', Collection::SORT_ORDER_ASC);
            $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
            $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);
            $this->setData('categories_collection', $collection);
        }

        return $collection;
    }

    /**
     * Get the category URL
     *
     * @param Category $category
     * @return string
     */
    public function getCategoryUrl(Category $category): string
    {
        if ($this->isTgEnv()) {
            return '/catalog/category/view/id/' . $category->getId();
        }

        return $category->getUrl();
    }
}
