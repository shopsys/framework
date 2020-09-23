<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

class FilepathComparator
{
    /**
     * @param string $path
     * @param string $directoryPath
     * @return bool
     */
    public function isPathWithinDirectory($path, $directoryPath)
    {
        $directoryPathRealpath = realpath($directoryPath);
        if ($directoryPathRealpath === false) {
            throw new \Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException(
                $directoryPath
            );
        }

        return $this->isPathWithinDirectoryRealpathRecursive($path, $directoryPathRealpath);
    }

    /**
     * @param string $path
     * @param string $directoryRealpath
     * @return bool
     */
    protected function isPathWithinDirectoryRealpathRecursive($path, $directoryRealpath)
    {
        if (realpath($path) === $directoryRealpath) {
            return true;
        }

        if ($this->hasAncestorPath($path)) {
            return $this->isPathWithinDirectoryRealpathRecursive(dirname($path), $directoryRealpath);
        }
        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function hasAncestorPath($path)
    {
        return dirname($path) !== $path;
    }
}
