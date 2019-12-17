<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

class UploadedFileData
{
    /**
     * @var string[]
     */
    public $uploadedFiles = [];

    /**
     * @var string[]
     */
    public $uploadedFilenames = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public $filesToDelete = [];

    /**
     * @var string[]
     */
    public $currentFilenamesIndexedById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public $orderedFiles = [];
}
