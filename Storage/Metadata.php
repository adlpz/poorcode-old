<?php

namespace Poorcode\Storage;

class Metadata
{

    private $dirname;

    private $basename;

    private $modification;

    private $size;

    public static function createFromFilename($postFile)
    {
        $new = new self;
        $new->setDirname(dirname($postFile));
        $new->setBasename(basename($postFile));
        $new->setModification(filemtime($postFile));
        $new->setSize(filemtime($postFile));

        return $new;
    }

    /**
     * @param mixed $basename
     */
    public function setBasename($basename)
    {
        $this->basename = $basename;
    }

    /**
     * @return mixed
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * @param mixed $dirname
     */
    public function setDirname($dirname)
    {
        $this->dirname = $dirname;
    }

    /**
     * @return mixed
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * @param mixed $modificatiom
     */
    public function setModification($modificatiom)
    {
        $this->modification = $modificatiom;
    }

    /**
     * @return mixed
     */
    public function getModification()
    {
        return $this->modification;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

} 