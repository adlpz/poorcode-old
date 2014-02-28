<?php

namespace Poorcode\Cache;

class Cache
{

    const HEADER_FORMAT = "LSS";
    const HEADER_FORMAT_UNPACK = "Ltimestamp/SpostCount/Smetadata";

    private $cacheFileDescriptor;

    private $postCount;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * 16 bits reserved for future use
     */
    private $metadata;

    public function __construct($cacheFile = null)
    {
        if (!is_null($cacheFile)) {
            $this->cacheFileDescriptor = fopen($cacheFile, 'rb');
            $this->unpackHeader(fread($this->cacheFileDescriptor, 8));
        }
    }

    private function unpackHeader($data)
    {
        $header = unpack(self::HEADER_FORMAT_UNPACK, $data);
        $this->lastUpdated = (new \DateTime())->setTimestamp($header['timestamp']);
        $this->postCount = $header['postCount'];
        $this->metadata = $header['metadata'];
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param \DateTime $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param mixed $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getPostCount()
    {
        return $this->postCount;
    }

    /**
     * @param mixed $postCount
     */
    public function setPostCount($postCount)
    {
        $this->postCount = $postCount;
    }

    /**
     * @param string $cacheFile
     * @param Post[] $posts
     */
    public function rebuild($cacheFile, array $posts)
    {
        $this->lastUpdated = new \DateTime();
        $this->postCount = count($posts);
        if (!is_null($this->cacheFileDescriptor)) {
            fclose($this->cacheFileDescriptor);
        }
        $cacheFileDescriptor = fopen($cacheFile, 'w');
        fwrite($cacheFileDescriptor, $this->packHeader());
        foreach ($posts as $post) {
            fwrite($cacheFileDescriptor, $post->pack());
        }
        fclose($cacheFileDescriptor);
        $this->cacheFileDescriptor = fopen($cacheFile, 'rb');
    }

    private function packHeader()
    {
        return pack(self::HEADER_FORMAT, $this->lastUpdated->getTimestamp(), $this->postCount, $this->metadata);
    }

    public function getPost($postId)
    {
        $this->jumpHeader();
        // Seek until find the ID
        while (!feof($this->cacheFileDescriptor)) {
            if (!Post::isDataAvailable($this->cacheFileDescriptor, 4)) {
                return null;
            }
            list($id, $size) = Post::unpackIdAndSize(fread($this->cacheFileDescriptor, 4));
            if ($id == $postId) {
                fseek($this->cacheFileDescriptor, -4, SEEK_CUR);
                $post = Post::createFromPackedRead($this->cacheFileDescriptor);
                fseek($this->cacheFileDescriptor, 0);
                return $post;
            }
            fseek($this->cacheFileDescriptor, $size + 124, SEEK_CUR);
        }
        fseek($this->cacheFileDescriptor, 0);
        return null;
    }

    private function jumpHeader()
    {
        fseek($this->cacheFileDescriptor, 8);
    }

    public function getPage($page, $count)
    {
        $this->seekToPage($page, $count);
        $posts = [];
        for ($i = 0; $i < $count; $i++) {
            $post = Post::createFromPackedRead($this->cacheFileDescriptor);
            if (is_null($post)) {
                break;
            }
            $posts[] = $post;
        }

        return $posts;

    }

    private function seekToPage($page, $count)
    {
        $this->jumpHeader();
        $skip = $page * $count;
        for ($i = 0; $i < $skip; $i++) {
            list($id, $size) = Post::unpackIdAndSize(fread($this->cacheFileDescriptor, 4));
            fseek($this->cacheFileDescriptor, 24 + $size);
        }
    }
} 