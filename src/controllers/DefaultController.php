<?php

namespace floriskoch\craftpatlib\controllers;

use Craft;
use craft\web\View;
use craft\web\Controller;
use DirectoryIterator;
use floriskoch\craftpatlib\CraftPatlib;
use Garp\Functional as f;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use yii\db\Exception;

class DefaultController extends Controller
{
    protected $componentsPath;

    protected $componentTree;

    protected $allowedTemplateExtensions;

    protected $settings;

    public function init()
    {
        $this->settings = CraftPatlib::getInstance()->getSettings();
        $this->allowedTemplateExtensions = ['twig'];
        $this->componentsPath = Craft::$app->view->getTemplatesPath() . '/' . $this->settings->componentRoot;
        $this->componentTree = $this->getComponentTree($this->componentsPath);

        $this->allowAnonymous = true;
    }

    public function actionIndex()
    {
        $variables['componentTree'] = $this->componentTree;

        $oldTemplateMode = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = Craft::$app->view->renderTemplate('craft-patlib/index', $variables);
        Craft::$app->view->setTemplateMode($oldTemplateMode);

        return $html;
    }

    public function actionShow(string $component)
    {
        $compPath = $this->getComponentPath($component, $this->componentTree, [$this->settings->componentRoot]);

        $variables = [
            'component' => $compPath,
            'assets' => $this->settings->assets
        ];

        return $this->renderTemplate('componentRenderer', $variables);
    }

    protected function getComponentTree($dir, $ignoreEmpty = false)
    {
        if (!$dir instanceof DirectoryIterator) {
            $dir = new DirectoryIterator((string)$dir);
        }
        $dirs = array();
        $files = array();
        foreach ($dir as $node) {
            if ($node->isDir() && !$node->isDot()) {
                $tree = $this->getComponentTree($node->getPathname(), $ignoreEmpty);
                if (!$ignoreEmpty || count($tree)) {
                    $dirs[$node->getFilename()] = $tree;
                }
            } elseif ($node->isFile()) {
                $name = $node->getFilename();
                $info = pathinfo($name);

                if (in_array($info['extension'], $this->allowedTemplateExtensions)) {
                    $files[] = $name;
                }
            }
        }
        asort($dirs);
        sort($files);
        return array_merge($dirs, $files);
    }

    protected function getComponentPath($needle, array $haystack, array $path = []) {
        $result = $this->getComponentPathArray($needle, $haystack, $path);
        if (!$result || !is_array($result)) {
            return false;
        }
        return implode('/', $result);
    }

    protected function getComponentPathArray($needle, array $haystack, array $path = [])
    {
        foreach ($haystack as $key => $value) {
            $currentPath = array_merge($path, [is_numeric($key) ? $value : $key]);
            if (is_array($value) && $result = $this->getComponentPathArray($needle, $value, $currentPath)) {
                return $result;
            } else if ($value === $needle) {
                return $currentPath;
            }
        }
        return false;
    }
}
