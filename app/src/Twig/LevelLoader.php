<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Twig;


class LevelLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    private $cache = array();

    private $paths;

    public function __construct($paths)
    {
        $this->paths = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return file_get_contents($this->findTemplate($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->findTemplate($name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return true;
        }

        try {
            $this->findTemplate($name);

            return true;
        } catch (\Twig_Error_Loader $exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->findTemplate($name)) <= $time;
    }

    protected function findTemplate($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->validateName($name);

        $seekingNamespace = null;
        $filename = $name;

        if (false !== $pos = strpos($name, ':')) {
            list($seekingNamespace, $filename) = explode(':', $name, 2);
        }

        $correctLevelOfNamespaceIsFound = false;
        foreach ($this->paths as $currentNamespace => $path) {
            // Go down to the desired namespace.
            if (null !== $seekingNamespace) {
                if ($currentNamespace === $seekingNamespace) {
                    $correctLevelOfNamespaceIsFound = true;
                }

                if (!$correctLevelOfNamespaceIsFound) {
                    continue;
                }
            }

            if (is_file($ffn = $path . '/' . $filename)) {
                return $this->cache[$name] = $path . '/' . $filename;
            }
        }

        throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $name));
    }

    protected function normalizeName($name)
    {
        return preg_replace('#/{2,}#', '/', strtr((string)$name, '\\', '/'));
    }

    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new \Twig_Error_Loader('A template name cannot contain NUL bytes.');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new \Twig_Error_Loader(sprintf('Looks like you try to load a template outside configured directories (%s).', $name));
            }
        }
    }
} 