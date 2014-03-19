<?php

namespace Poorcode\Storage;

use Poorcode\Cache\Post;

class Manager {

    private $postDirectory;

    public function __construct($postDirectory)
    {
        $this->postDirectory = $postDirectory;
    }

    /**
     * @return Metadata[]
     */
    public function getAllPostMetadata()
    {
        $postFiles = $this->getAllPostFiles();

        $metadataArray = [];
        foreach ($postFiles as $postFile) {
            $metadataArray[] = Metadata::createFromFilename($postFile);
        }
        return $metadataArray;
    }

    /**
     * @return string[]
     */
    private function getAllPostFiles()
    {
        return glob($this->postDirectory . "/*.txt");
    }

    public function getAllPosts()
    {
        $postFiles = $this->getAllPostFiles();
        $posts = [];
        foreach ($postFiles as $postFile) {
            $posts[] = Post::createFromPlainText(file_get_contents($postFile));
        }
		usort($posts, function(Post $a, Post $b) { return $a->getId() >= $b->getId() ? -1 : 1; });
        return $posts;

    }
} 
