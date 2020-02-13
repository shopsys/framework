<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

class IndexDefinitionLoader
{
    /**
     * @var string
     */
    protected $indexDefinitionsDirectory;

    /**
     * @var string
     */
    protected $indexPrefix;

    /**
     * @param string $indexDefinitionsDirectory
     * @param string $indexPrefix
     */
    public function __construct(string $indexDefinitionsDirectory, string $indexPrefix)
    {
        $this->indexDefinitionsDirectory = $indexDefinitionsDirectory;
        $this->indexPrefix = $indexPrefix;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex $index
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition
     */
    public function getIndexDefinition(AbstractIndex $index, int $domainId): IndexDefinition
    {
        return new IndexDefinition($index, $this->indexDefinitionsDirectory, $this->indexPrefix, $domainId);
    }
}
