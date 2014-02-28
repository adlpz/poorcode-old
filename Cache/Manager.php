<?php

namespace Poorcode\Cache;

use Poorcode\Storage\Manager as StorageManager;

class Manager
{

    /**
     * @var \Poorcode\Storage\Manager
     */
    private $storage;

    private $cacheFile;

    private $cache;

    function __construct(StorageManager $storage, $cacheFile)
    {
        if (!file_exists($cacheFile) || !(filesize($cacheFile) >= 8)) {
            touch($cacheFile);
            $this->cache = new Cache();
            $this->cache->rebuild($cacheFile, $storage->getAllPosts());
        }
        $this->cache = new Cache($cacheFile);
        $this->storage = $storage;
        $this->cacheFile = $cacheFile;
    }

    public function validate()
    {
        $cacheUpdateTimestamp = $this->cache->getLastUpdated()->getTimestamp();
        $files = $this->storage->getAllPostMetadata();
        foreach ($files as $file) {
            if ($file->getModification() > $cacheUpdateTimestamp) {
                // Cache is old. Rebuild
                $this->cache->rebuild($this->cacheFile, $this->storage->getAllPosts());
            }
        }

    }

    public function get()
    {
        return $this->cache;
    }
}