<?php

namespace ywsing\CrudeForum;

use \ywsing\CrudeForum\Iterator\FileObject;
use \FastRoute\Dispatcher;
use \Twig\Environment;
use \Twig\TwigFunction;
use \Twig\Loader\FilesystemLoader;

class Core {

    private $storage;
    private $administrator;
    private $baseURL;
    private $basePath;
    public $template;

    public function __construct($config) {
        $this->storage = new Storage($config);
        $this->administrator = $config['administrator'] ?? '';
        $this->baseURL = $config['baseURL'];
        $this->basePath = rtrim($config['basePath'] ?? '/', '/');
        $this->template = new Environment(
            new FilesystemLoader(__DIR__ . '/../views'),
            [
                //'cache' => __DIR__ . '/../data/cache/twig',
            ]);
        $this->template->addFunction(new TwigFunction('str_repeat', 'str_repeat'));
        $this->template->addFunction(new TwigFunction('linkTo',
            array(&$this, 'linkTo')
        ));
        $this->template->addFunction(new TwigFunction('postRssPubDate', function ($postTime) {
            return trim(preg_replace('/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)/', '$1, $3 $2 $7 $4:$5:$6 +0800', $postTime));
        }));
    }

    public function isAdmin(string $user) {
        return (!empty($user) && $this->administrator === $user);
    }

    public function setBaseUrl($baseURL) {
        $this->baseURL = rtrim($baseURL, '/');
    }

    public function setBasePath($basePath) {
        $this->basePath = rtrim($basePath, '/');
    }

    public static function defaultBaseURL() {
        $host = $_SERVER['SERVER_NAME'];
        $scheme = strtolower($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $port = $_SERVER['SERVER_PORT'];
        if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)) {
            return "{$scheme}://{$host}:${port}";
        }
        return "{$scheme}://{$host}";
    }

    public static function defaultBasePath() {
        $filename = basename($_SERVER['SCRIPT_NAME'], '.php');
        return ($filename === 'index') ?
            dirname($_SERVER['SCRIPT_NAME']) : $_SERVER['SCRIPT_NAME'];
    }

    public function linkTo(string $entity, $id=NULL, $action=NULL, $absolute=FALSE) {
        $path = ($absolute) ? [$this->baseURL, $entity] : [$this->basePath, $entity];
        if (!empty($id)) $path[] = $id;
        if (!empty($action)) $path[] = $action;

        // handle exceptions
        switch ($entity) {
            case 'post':
                // saving post with no postID equals create new post
                if (empty($id) && $action === 'save') {
                    array_pop($path);
                    array_push('add');
                }
        }
        return implode('/', $path);
    }

    public static function bootstrap(Dispatcher $dispatcher, Core $forum, callable $route) {
        list($httpMethod, $uri) = $route();
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                http_response_code(404);
                die('not found');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                http_response_code(405);
                die('method not allowed');
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                try {
                    $handler($vars, $forum);
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
                break;
        }
    }

    public static function routeURI() {
        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query; string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        return array($httpMethod, $uri);
    }

    public static function routeQueryString($basename='/', $suffix=NULL) {
        return function () use ($basename, $suffix) {
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $queryString = rawurldecode($_SERVER['QUERY_STRING'] ?? '');
            $queryString = !empty($queryString) ? '/' . $queryString : '';
            $suffix = !empty($suffix) ? '/' . $suffix : '';
            return array($httpMethod, $basename . $queryString . $suffix);
        };
    }

    public static function routePathInfo() {
        return function () {
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $pathInfo = $_SERVER['PATH_INFO'] ?? '/';
            return array($httpMethod, $pathInfo);
        };
    }

    public function getIndex(): ?ForumIndex {
        return $this->storage->getIndex();
    }

    public function getCount(): int {
        return $this->storage->getCount();
    }

    public function incCount() {
        return $this->storage->incCount();
    }

    public function readPost(string $postID): ?Post {
        return $this->storage->readPost($postID);
    }

    public function writePost(int $postID, Post $post) {
        return $this->storage->writePost($postID, $post);
    }

    public function appendIndex(PostSummary $postSummary, $parentID=FALSE) {
        return $this->storage->appendIndex($postSummary, $parentID);
    }

    public function getLock() {
        return $this->storage->getLock();
    }

    public function readPrevPostSummary(string $postID): ?PostSummary {
        $prevSummary = NULL;
        try {
            $index = $this->storage->getIndex();
            foreach ($index as $postSummary) {
                if ($postSummary->id == $postID) {
                    if ($prevSummary !== NULL) return $prevSummary;
                    throw new \Exception("post #{$postID} has no previous post");
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function readNextPostSummary(string $postID): ?PostSummary {
        $prevSummary = NULL;
        try {
            $index = $this->storage->getIndex();
            foreach ($index as $postSummary) {
                if (($prevSummary !== NULL) && ($prevSummary->id == $postID)) {
                    return $postSummary;
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function readPostSummary(string $postID): ?PostSummary {
        try {
            $index = $this->storage->getIndex();
            foreach ($index as $postSummary)
                if ($postSummary->id == $postID) return $postSummary;
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function log($msg) {

        // get the forumName of the current user
        if(!isset ($_COOKIE["forumName"])) {
            if(!isset ($_COOKIE["forumCDROM"])) {
                $user = rand (0, 16384);
                setcookie ("forumCDROM", $user, mktime (0, 0, 0, 1, 1, 2038), "/");
            } else $user = $_COOKIE["forumCDROM"];
        }
        else $user = $_COOKIE["forumName"];
        $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? '';

        // context of the request
        $context = array(
            'time' => strftime("%a %b %d %X %Y"),
            'remoteAddr' => $_SERVER["REMOTE_ADDR"] ?? '',
            'xForwardedFor' => $_SERVER["HTTP_X_FORWARDED_FOR"] ?? '',
            'user' => $user,
            'userAgent' => $userAgent,
        );

        if(
            /* Do not log me */
            (empty($this->administrator) || ($user != $this->administrator)) &&

            /* Do not log bots */
            !is_string(strstr($userAgent, "http://search.msn.com/msnbot.htm")) &&
            !is_string(strstr($userAgent, "Free Eating Union")) &&
            !is_string(strstr($userAgent, "ia_archiver")) &&
            !is_string(strstr($userAgent, "sogou spider")) &&
            !is_string(strstr($userAgent, "Baiduspider+(+")) &&
            !is_string(strstr($userAgent, "Yahoo! Slurp")) &&
            !is_string(strstr($userAgent, "Googlebot"))
        ) {
            // log the message to storage
            $this->storage->writeLog($context, $msg);
        }
    }

}
