<?php

namespace Poorcode;

use Poorcode\Cache\Manager as CacheManager;
use Poorcode\Exception\NotFoundException;
use Poorcode\Router\Router;
use Poorcode\Storage\Manager as StorageManager;
use Poorcode\Templating\Renderer;

class Blog {

    const POSTS_PER_PAGE = 10;

    private $storageManager;
    private $cacheManager;
    private $cache;
    private $router;
    private $renderer;

    private $env = [
        'base_url' => 'http://poorcode.dev'
    ];


    function __construct($postDirectory, $cacheFile)
    {
        $this->storageManager = new StorageManager($postDirectory);
        $this->cacheManager = new CacheManager($this->storageManager, $cacheFile);
        $this->cacheManager->validate();
        $this->cache = $this->cacheManager->get();
        $this->router = new Router();
        $this->renderer = new Renderer();

        $this->setUpRoutes();

        $this->env['last_cache_build'] = $this->cache->getLastUpdated();
    }

    public function run()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $documentUri = $_SERVER['DOCUMENT_URI'];
        $pattern = "/^" . str_replace("/", "\\/", preg_quote($documentUri)) . "/";
        $route = preg_replace($pattern, "", $requestUri);
        return $this->router->route($route);
    }

    public function pageAction($page, $count = self::POSTS_PER_PAGE)
    {
        return $this->renderer->render('./Templates/page.html',
            array_merge($this->env,
                [
                    'posts' => $this->cache->getPage($page, $count),
                    'next_page' => $page + 1,
                    'prev_page' => ($page - 1) >= 0 ? $page - 1 : $page
                ]
            )
        );
    }

    public function postAction($id)
    {
        $post = $this->cache->getPost($id);
        if (is_null($post)) {
            throw new NotFoundException("No post with id $id found");
        }
        return $this->renderer->render('./Templates/single.html',
            array_merge($this->env, ['posts' => [$post]])
        );
    }

    public function indexAction()
    {
        return $this->pageAction(0);
    }

    private function setUpRoutes()
    {
        $this->router->register('/', 'indexAction', $this);
        $this->router->register('/post/$id', 'postAction', $this);
        $this->router->register('/$page', 'pageAction', $this);
    }
}
