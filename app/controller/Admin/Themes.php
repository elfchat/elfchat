<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Controller\Admin;

use ElfChat\Controller;
use ElfChat\Theme\Theme;
use Silicone\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/themes")
 */
class Themes extends Controller
{
    /**
     * @return Theme[]
     */
    private function getThemes()
    {
        $finder = new Finder();
        $finder->files()
            ->depth(1)
            ->name(Theme::CONFIG_NAME)
            ->sort(function ($a, $b) {
                return ($a->getMTime() < $b->getMTime());
            })
            ->in($this->app->getThemeDir());

        $themes = array();
        foreach ($finder as $file) {
            $theme = new Theme($file);
            $theme->installed = $this->app->config()->get('theme') === $theme->name;
            $themes[$theme->name] = $theme;
        }

        return $themes;
    }

    /**
     * @Route("", name="admin_themes")
     */
    public function index()
    {
        return $this->render('admin/theme/list.twig', array(
            'themes' => $this->getThemes(),
        ));
    }

    /**
     * @Route("/install", name="admin_theme_install")
     */
    public function install(Request $request)
    {
        $themes = $this->getThemes();

        $name = $request->get('name');
        if (isset($themes[$name])) {
            $config = $this->app->config();
            $config->set('theme', $themes[$name]->name);
            $config->set('theme_path', $themes[$name]->getThemeViewsPath());
            $config->save();
        }

        //$this->installPlugins($themes);

        return $this->app->redirect($this->app->url('admin_themes'));
    }
} 