<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

class EntityLogData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum|null
     */
    public $action = null;

    /**
     * @var string|null
     */
    public $userIdentifier = null;

    /**
     * @var string|null
     */
    public $entityName = null;

    /**
     * @var int|null
     */
    public $entityId = null;

    /**
     * @var string|null
     */
    public $entityIdentifier = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogSourceEnum|null
     */
    public $source = null;

    /**
     * @var array
     */
    public $changeSet = [];

    /**
     * @var string|null
     */
    public $parentEntityName = null;

    /**
     * @var int|null
     */
    public $parentEntityId = null;

    /**
     * @var string|null
     */
    public $logCollectionNumber = null;

    /**
     * @var \DateTime|null
     */
    public $createdAt = null;
}
