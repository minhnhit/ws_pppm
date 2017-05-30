<?php
namespace App\Option;

use DirectoryIterator;
use Zend\Stdlib\AbstractOptions;

/**
 * ModuleOptions
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Array of paths
     *
     * @var array
     */
    protected $paths;
    /**
     * Key - Value array for resource details
     *
     * @var array
     */
    protected $resourceOptions;
    /**
     * Set an array of paths where the files to be scanned by Swagger are searched
     *
     * @param  array $paths
     * @throws \RuntimeException
     * @return ModuleOptions
     */
    public function setPaths(array $paths)
    {
        if (count($paths) < 1) {
            throw new \RuntimeException('No path(s) were specified for SwaggerModule');
        }
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                throw new \RuntimeException(sprintf(
                    'Path %s given to SwaggerModule is invalid',
                    $path
                ));
            }
        }
        $this->paths = $paths;
        return $this;
    }
    /**
     * Get the array of paths where to files to be scanned by Swagger are searched
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
    /**
     * Get a list of files to be scanned by Swagger
     *
     * @return array
     */
    public function getFileList()
    {
        $fileList = [];
        foreach ($this->paths as $path) {
            $directoryIterator = new DirectoryIterator($path);
            /** @var $file DirectoryIterator */
            foreach ($directoryIterator as $file) {
                if (!$file->isDir()) {
                    $fileList[] = $file->getPathname();
                }
            }
        }
        return $fileList;
    }
    /**
     *
     * @return array
     */
    public function getResourceOptions()
    {
        return $this->resourceOptions;
    }
    /**
     *
     * @param array $resourceOptions
     */
    public function setResourceOptions($resourceOptions)
    {
        $this->resourceOptions = $resourceOptions;
    }
}
