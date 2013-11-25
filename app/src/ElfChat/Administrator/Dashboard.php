<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Administrator;

use Silicone\Controller;
use Silicone\Route;

/**
 * @Route("/admin")
 */
class Dashboard extends Controller
{
    /**
     * @Route("/home", name="admin")
     */
    public function index()
    {
        return $this->render('admin/dashboard/index.twig');
    }

    /**
     * @Route("")
     */
    public function admin()
    {
        return $this->app->redirect($this->app->url('admin'));
    }
}