<?php

namespace Poorcode\Cache;

use Adlpz\Minidown\Markdown;

class Post {

    const ID_AND_SIZE_FORMAT = "SS";
    const ID_AND_SIZE_FORMAT_UNPACK = "Sid/Slength";
    const DATE_AND_TITLE_FORMAT = "La120";
    const HEADER_FORMAT = "SSLa120";
    const HEADER_FORMAT_UNPACK = "Sid/Slength/Ltimestamp/a120title";
    const CONTENT_FORMAT = "a*";
    const CONTENT_FORMAT_UNPACK = "a*content";

    private $id;
    private $title;

    /**
     * @var \DateTime
     */
    private $date;
    private $content;

    public static function createFromPlainText($plaintextPost)
    {
        $parts = explode(PHP_EOL, $plaintextPost);
        $id  = intval($parts[0]);
        $title = $parts[1];
        $datetime = new \DateTime($parts[2]);
        $content = implode("\n", array_slice($parts, 3, null, false));

        $new = new self();
        $new->setId($id);
        $new->setDate($datetime);
        $new->setTitle($title);
        $new->setContent(Markdown::markdown($content));

        return $new;
    }

    public static function createFromPackedRead($cacheFileHandler)
    {
        if (feof($cacheFileHandler)) {
            return null;
        }
        if (!(self::isDataAvailable($cacheFileHandler, 128))) {
            return null;
        }
        list($id, $size, $date, $title) = self::unpackHeader(fread($cacheFileHandler, 128));
        if (feof($cacheFileHandler)) {
            // There's nothing after the header (??)
            return null;
        }
        $content = unpack(self::CONTENT_FORMAT_UNPACK, fread($cacheFileHandler, $size));
        $new = new self;
        $new->setId($id);
        $new->setTitle(trim($title));
        $new->setDate($date);
        $new->setContent($content['content']);
        return $new;
    }

    public function pack()
    {
        // Trim to (128-2-2-4)bytes the title
        $this->title = str_split($this->title, 120)[0];

        /*
         * Format: FIXME: NOPE, NOT ANYMORE!!! Now 16 + 16 + 32 + 960 = 1028 bit = 128 byte header.
         * 0   8  16  24  32  40  48  56  64  72  80  88  96 104 112 120 128 136 144 152 160 168 176 184 192 200 208 216 224 240 2048 256
         * |  ID   |  SIZE |   DATETIME    |                                           TITLE                                            |
         * |                                                            DATA                                                            |
         * |                                                            DATA                                                            |
         * |                                                            ....                                                            |
         */
        $format = self::HEADER_FORMAT . self::CONTENT_FORMAT;
        return pack($format, $this->id, strlen($this->content), $this->date->getTimestamp(), $this->title, $this->content);
    }

    public static function unpackIdAndSize($data)
    {
        $idAndSize = unpack(self::ID_AND_SIZE_FORMAT_UNPACK, $data);
        return [$idAndSize['id'], $idAndSize['length']];
    }

    public static function unpackHeader($data)
    {
        $header = unpack(self::HEADER_FORMAT_UNPACK, $data);
        $datetime = (new \DateTime())->setTimestamp($header['timestamp']);
        return [
            $header['id'],
            $header['length'],
            $datetime,
            $header['title']
        ];
    }

    public static function isDataAvailable($fileDescriptor, $bytes)
    {
        $currentCursor = ftell($fileDescriptor);
        for ($seeked = 0; $seeked < $bytes; $seeked++) {
            fgetc($fileDescriptor);
            if (feof($fileDescriptor)) {
                return false;
            }
        }
        fseek($fileDescriptor, $currentCursor);
        return true;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}