<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Avatar extends File
{
    /**
     * @Assert\File(maxSize="2M", mimeTypes={"image/png", "image/jpeg"}, groups={"avatar"})
     */
    protected $file;

    public function __toString()
    {
        return (string)$this->getUrl();
    }

    public function generatePath()
    {
        $filename = sha1(uniqid(mt_rand(), true) . $this->getFile()->getClientOriginalName());
        $prefix = substr($filename, 0, 2) . '/' . substr($filename, 2, 2) . '/';
        return $prefix . $filename . '.' . $this->getFile()->guessExtension();
    }
}