<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

/**
 * @ORM\Table(name="uploaded_files_relations",
 *     indexes={
 *          @ORM\Index(columns={"entity_name", "entity_id", "type"}),
 *          @ORM\Index(columns={"entity_name", "entity_id", "type", "uploaded_file_id"}),
 *     })
 * @ORM\Entity
 */
class UploadedFileRelation
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $entityName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile")
     * @ORM\JoinColumn(name="uploaded_file_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $uploadedFile;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $type;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @param int $position
     * @param string $type
     */
    public function __construct(
        string $entityName,
        int $entityId,
        UploadedFile $uploadedFile,
        int $position = 0,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ) {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->uploadedFile = $uploadedFile;
        $this->position = $position;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
