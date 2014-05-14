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
 * @Route("/install")
 */
class Install extends Controller
{
    /**
     * @Route("/check.js")
     */
    public function check()
    {
        return $this->app->json(true);
    }

    /**
     * @Route("", name="install_configuration")
     */
    public function configuration()
    {
        $config = $this->app->config();

        $config->set('baseurl', $this->request->getSchemeAndHttpHost() . $this->request->getBasePath());
        $config->set('sqlite.path', $this->app->getOpenDir() . 'elfchat.db');
        $config->set('server.host', $this->request->getHost());

        $form = $this->app->form($config)
            ->add('locale', 'choice', array(
                'choices' => array(
                    'ru' => 'Russian',
                    'en' => 'English',
                ),
                'label' => 'Language',
            ))
            ->add('baseurl', 'text', array('label' => 'Base URL'))
            ->add('database', 'choice', array(
                'choices' => array(
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'postgres' => 'PostgreSQL'
                ),
                'label' => 'Database',
            ))

            ->add('mysql:host', 'text', array('label' => 'Host'))
            ->add('mysql:user', 'text', array('label' => 'User'))
            ->add('mysql:password', 'text', array('label' => 'Password', 'required' => false))
            ->add('mysql:dbname', 'text', array('label' => 'Database Name'))

            ->add('sqlite:path', 'text', array('label' => 'Path'))
            ->add('sqlite:user', 'text', array('label' => 'User', 'required' => false))
            ->add('sqlite:password', 'text', array('label' => 'Password', 'required' => false))

            ->add('postgres:host', 'text', array('label' => 'Host'))
            ->add('postgres:user', 'text', array('label' => 'User'))
            ->add('postgres:password', 'text', array('label' => 'Password', 'required' => false))
            ->add('postgres:dbname', 'text', array('label' => 'Database Name'))
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isValid()) {
            $config = $form->getData();
            $config->save();

            return $this->app->redirect($this->app->url('install_proxy'));
        }

        return $this->app->render('install/config.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/proxy", name="install_proxy")
     */
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

        return $this->app->redirect($this->app->url('install_database'));
    }

    /**
     * @Route("/database", name="install_database")
     */
    public function database()
    {
        try {

            $em = $this->app->entityManager();
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        } catch (ToolsException $e) {
            return $this->app->render('install/error.twig', array(
                'message' => $e->getMessage(),
            ));
        } catch (\PDOException $e) {
            return $this->app->render('install/error.twig', array(
                'message' => $e->getMessage(),
            ));
        }

        return $this->app->redirect($this->app->url('install_admin'));
    }

    /**
     * @Route("/admin", name="install_admin")
     */
    public function admin()
    {
        $form = $this->app->formType(new RegistrationFormType());

        $form->handleRequest($this->request);
        if ($form->isValid()) {
            /** @var $user \ElfChat\Entity\User */
            $user = $form->getData();
            $user->role = 'ROLE_ADMIN';
            $user->save();

            // Login
            $this->app->session()->set('user', array($user->id, $user->role));

            // Set ElfChat in installed state
            $this->app->config()->set('installed', true);
            $this->app->config()->save();

            return $this->app->redirect($this->app->url('chat'));
        }

        return $this->app->render('install/admin.twig', array(
            'form' => $form->createView(),
        ));
    }
} 