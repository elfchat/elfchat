<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Controller;
use Silicone\Route;

/**
 * @Route("/admin/plugins")
 */
class Plugins extends Controller
{
    /**
     * @Route("", name="admin_plugins")
     */
    public function index()
    {
        return 'In development.';
    }
} 