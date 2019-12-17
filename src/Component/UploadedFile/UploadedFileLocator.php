<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

class UploadedFileLocator
{
    /**
     * @var string
     */
    protected $uploadedFileDir;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @param string $uploadedFileDir
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        string $uploadedFileDir,
        FilesystemInterface $filesystem,
        DomainRouterFactory $domainRouterFactory
    ) {
        $this->uploadedFileDir = $uploadedFileDir;
        $this->filesystem = $filesystem;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getRelativeUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getRelativeFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getAbsoluteFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        if ($this->fileExists($uploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_download_uploaded_file', [
                'uploadedFileId' => $uploadedFile->getId(),
                'uploadedFilename' => $uploadedFile->getSlugWithExtension(),
            ]);
        }

        throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return bool
     */
    public function fileExists(UploadedFile $uploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($uploadedFile);

        return $this->filesystem->has($fileFilepath);
    }

    /**
     * @param string $entityName
     * @return string
     */
    protected function getRelativeFilePath(string $entityName): string
    {
        return $entityName;
    }

    /**
     * @param string $entityName
     * @return string
     */
    public function getAbsoluteFilePath(string $entityName): string
    {
        return $this->uploadedFileDir . $this->getRelativeFilePath($entityName);
    }
}
