<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Closure;

/**
 * The class encapsulates Category along with it's visible children
 * that are lazy loaded to prevent unnecessary SQL queries.
 * It is used for rendering front-end category tree.
 *
 * @see \Shopsys\FrameworkBundle\Model\Category\Category
 */
class CategoryWithLazyLoadedVisibleChildren
{
    protected bool $hasChildren;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]|null
     */
    protected ?array $children = null;

    /**
     * @param \Closure $lazyLoadChildrenCallback
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param bool $hasChildren
     */
    public function __construct(
        protected readonly Closure $lazyLoadChildrenCallback,
        protected readonly Category $category,
        $hasChildren,
    ) {
        $this->hasChildren = $hasChildren;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]
     */
    public function getChildren()
    {
        if ($this->children === null) {
            $this->children = call_user_func($this->lazyLoadChildrenCallback);
        }

        return $this->children;
    }
}
