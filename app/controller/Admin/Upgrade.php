<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use Doctrine\ORM\Tools\ToolsException;
use ElfChat\Controller;
use ElfChat\Form\RegistrationFormType;
use Silicone\Route;

/**
 * @Route("/upgrade")
 */
class Upgrade extends Controller
{
    /**
     * @Route("", name="upgrade")
     */
    public function start()
    {
        $this->openChat(false);
        return $this->app->redirect($this->app->url('upgrade_finish'));
    }

    /**
     * @Route("/finish", name="upgrade_finish")
     */
    public function finish()
    {
        $this->clearCache();
        $this->database();
        $this->proxy();
        $this->plugins();
        $this->openChat(true);
        return $this->app->render('admin/upgrade/finish.twig');
    }

    public function openChat($open)
    {
        $config = $this->app->config();
        $config->set('is_chat_open', $open);
        $config->save();
    }

    public function clearCache()
    {
        $it = new \RecursiveDirectoryIterator($this->app->getCacheDir());
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }

    public function database()
    {
        $em = $this->app->entityManager();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata());
    }

    public function proxy()
    {
        $em = $this->app->entityManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $proxyDir = $em->getConfiguration()->getProxyDir();

        if (!is_dir($proxyDir)) {
            mkdir($proxyDir, 0777, true);
        }

        $proxyDir = realpath($proxyDir);

        if (!file_exists($proxyDir)) {
            throw new \InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not exist.", $em->getConfiguration()->getProxyDir())
            );
        }

        if (!is_writable($proxyDir)) {
            throw new \InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not have write permissions.", $proxyDir)
            );
        }

        if (count($metadatas)) {
            // Generating Proxies
            $em->getProxyFactory()->generateProxyClasses($metadatas, $proxyDir);
        }
    }

    public function plugins()
    {
        $pm = $this->app['plugin_manager'];
        $pm->install();
    }
} 