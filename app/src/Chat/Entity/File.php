<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"file" = "Chat\Entity\File", "avatar" = "Chat\Entity\Avatar"})
 * @ORM\Table("elfchat_uploads")
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $mimetype;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $extension;

    /**
     * @Assert\File(maxSize="2M")
     * @var UploadedFile
     */
    protected $file;

    /**
     * Temporary file path for deleting later.
     * @var string
     */
    private $deleteFilePath;

    /**
     * Server path to upload directory.
     * @var string
     */
    static private $uploadPath;

    /**
     * Web url to upload directory.
     * @var string
     */
    static private $baseUrl;

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : self::getUploadPath() . '/' . $this->path;
    }

    public function getUrl()
    {
        return null === $this->path
            ? null
            : self::getBaseUrl() . '/' . $this->path;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // Change path member for Unit of Work of Doctrine.
        if (isset($this->path)) {
            $this->deleteFilePath = $this->getAbsolutePath();
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            $this->path = $this->generatePath();
            $this->filename = $this->getFile()->getClientOriginalName();
            $this->extension = $this->getFile()->getClientOriginalExtension();
            $this->mimetype = $this->getFile()->getClientMimeType();
        }
    }

    public function generatePath()
    {
        $filename = sha1(uniqid(mt_rand(), true) . $this->getFile()->getClientOriginalName());
        $prefix = substr($filename, 0, 2) . '/' . substr($filename, 2, 2) . '/';
        return  $prefix . $filename . '.file';
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        $explodedPath = explode('/', $this->path);
        $filename = array_pop($explodedPath);
        $directory = implode('/', $explodedPath);

        $this->getFile()->move(
            $this->getUploadPath() . '/' . $directory,
            $filename
        );

        // check if we have an old image
        if (isset($this->deleteFilePath)) {
            if (is_file($this->deleteFilePath)) {
                unlink($this->deleteFilePath);
            }
            $this->deleteFilePath = null;
        }

        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $directoryPath
     */
    public static function setUploadPath($directoryPath)
    {
        self::$uploadPath = $directoryPath;
    }

    /**
     * @return string
     */
    public static function getUploadPath()
    {
        if(empty(self::$uploadPath)) {
            throw new \RuntimeException('Upload path does not specified for File class.');
        }
        return self::$uploadPath;
    }

    /**
     * @param string $directoryUrl
     */
    public static function setBaseUrl($directoryUrl)
    {
        self::$baseUrl = $directoryUrl;
    }

    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        if(empty(self::$baseUrl)) {
            throw new \RuntimeException('Base url does not specified for File class.');
        }
        return self::$baseUrl;
    }

    public function __toString()
    {
        return (string)$this->filename;
    }

    /**
     * This method need for properly work of Security Component.
     * Serialization of UploadedFile class is not allowed.
     *
     * @return array
     */
    public function __sleep()
    {
        return array('id', 'path', 'filename', 'mimetype', 'extension');
    }
}