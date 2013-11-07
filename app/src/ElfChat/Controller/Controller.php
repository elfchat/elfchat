<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller;

use ElfChat\Application;
use Silicone\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }


}