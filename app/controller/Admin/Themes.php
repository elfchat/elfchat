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
use Symfony\Component\Finder\SplFileInfo;
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
        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $theme = new Theme($file);
            $theme->setWebPath($this->app->request()->getBasePath() . '/theme/' . $file->getRelativePath());
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
            /** @var $theme Theme */
            $theme = $themes[$name];
            $config = $this->app->config();

            $config->set('theme.name', $theme->name);
            $config->set('theme.views', $theme->getViews());
            $config->set('theme.assets_dir', $theme->getDir() . '/' . $theme->getAssets());
            $config->set('theme.assets_webpath', $theme->getWebPath() . '/' . $theme->getAssets());

            $config->save();
        }

        return $this->app->redirect($this->app->url('admin_themes'));
    }
} 