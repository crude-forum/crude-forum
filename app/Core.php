<?php

/**
 * Bootstrapping the main objects to use in the forum
 *
 * PHP Version 7.1
 *
 * @file     FileStorage.php
 * @category File
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

namespace CrudeForum\CrudeForum;

use \DI\Container;
use \FastRoute\Dispatcher;
use \Twig\Environment;
use \Twig\TwigFunction;
use \Symfony\Component\Dotenv\Dotenv;
use \CrudeForum\CrudeForum\Exception\PostNotFound;
use \Generator;

/**
 * Core provides access for bootstraping the forum.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

class Core
{

    private $_storage;
    private $_administrator;
    private $_baseURL;
    private $_basePath;
    public $template;

    /**
     * Class constructor
     *
     * @param FileStorage $storage Storage engine to use.
     * @param Environment $twig    Twig template engine.
     * @param array       $config  configurations for the forum.
     */
    public function __construct(
        Storage $storage,
        Environment $twig,
        Config $configs,
        array $options = [],
    )
    {

        // add helper functions
        $twig->addFunction(new TwigFunction('linkTo', $this->linkTo(...)));
        $twig->addFunction(new TwigFunction('str_repeat', \str_repeat(...)));
        $twig->addFunction(
            new TwigFunction(
                'postRssPubDate',
                function ($postTime) {
                    return trim(
                        preg_replace(
                            '/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)/',
                            '$1, $3 $2 $7 $4:$5:$6 +0800',
                            $postTime
                        )
                    );
                }
            )
        );

        // assign template engine
        $this->template = $twig;

        // assign storage engine
        $this->_storage = $storage;

        // assign config parameters
        $this->_administrator = $options['administrator'] ?? '';
        $this->_baseURL = $configs->baseURL;
        $this->_basePath = $configs->basePath;
    }

    /**
     * Deterine if a given user name is the configured administrator
     *
     * @param string $user The name of the user.
     *
     * @return boolean
     */
    public function isAdmin(string $user)
    {
        return (!empty($user) && $this->_administrator === $user);
    }

    /**
     * Set the default base URL
     *
     * @param string $baseURL Base URL to set
     *
     * @return void
     */
    public function setBaseUrl($baseURL)
    {
        $this->_baseURL = rtrim($baseURL, '/');
    }

    /**
     * Set the default base path
     *
     * @param string $basePath Base Path to set
     *
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->_basePath = rtrim($basePath, '/');
    }

    /**
     * Return the configured default base URL.
     *
     * @return string
     */
    public static function defaultBaseURL(): string
    {
        $host = $_SERVER['SERVER_NAME'];
        $scheme = strtolower($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $port = $_SERVER['SERVER_PORT'];
        if (($scheme == 'http' && $port != 80)
            || ($scheme == 'https' && $port != 443)
        ) {
            return "{$scheme}://{$host}:{$port}";
        }
        return "{$scheme}://{$host}";
    }

    /**
     * Return the configured default base path.
     *
     * @return string
     */
    public static function defaultBasePath(): string
    {
        $filename = basename($_SERVER['SCRIPT_NAME'], '.php');
        return ($filename === 'index') ?
            dirname($_SERVER['SCRIPT_NAME']) : $_SERVER['SCRIPT_NAME'];
    }


    /**
     * Load environment variables or .env file
     *
     * @param string $dir The installation dir of CrudeForum.
     *
     * @return Dotenv The Dotenv instance for further operations.
     */
    public static function loadDotenv(string $dir): Dotenv
    {
        // load environment variable as config
        $dotenv = (new Dotenv())->usePutenv();
        $dotenv->populate(
            [
                'CRUDE_DIR' => $dir,
                'CRUDE_ADMIN' => '',
            ]
        ); // populate default variables that requires PHP computations

        // load .env.dist default
        $dotenv->load($dir.'/.env.dist');

        // load user overrides
        if (file_exists($dir . '/.env')) {
            $dotenv->load($dir . '/.env'); // load .env, if exists
        }
        return $dotenv;
    }

    /**
     * Get environment variable loaded by Core::envLoad
     *
     * @param string      $name    Environment variable name to search for.
     * @param string|null $default Default value, if no env is set.
     *
     * @return string|null The variable string value, or null if not exists.
     */
    public static function env(string $name, ?string $default=null): ?string
    {
        if (($value = getenv($name)) === false) {
            return $default;
        }
        return $value;
    }

    /**
     * Bootstrap the routing with dispatcher, forum object and the route function.
     *
     * @param Container $container
     *     Container instance for getting dispatcher, forum object and environment configs.
     * @param callable $route
     *     Route function that returns request method and path for route.
     *     Function should retrun array of [method, path].
     *
     * @return void
     */
    public static function bootstrap(
        Container $container,
        callable $route,
    ) {

        /** @var Dispatcher $dispatcher */
        $dispatcher = $container->get('dispatcher');
        /** @var Core $forum */
        $forum = $container->get('forum');
        /** @var array $configs */
        $configs = $container->get('configs');

        list($httpMethod, $uri) = $route();
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            // ... 404 Not Found
            http_response_code(404);
            echo $forum->template->render(
                'error.twig',
                [
                    'title' => 'not found',
                    'configs' => $configs,
                    'message' => 'not found',
                ]
            );
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            // ... 405 Method Not Allowed
            http_response_code(405);
            echo $forum->template->render(
                'error.twig',
                [
                    'title' => 'method not allowed',
                    'configs' => $configs,
                    'message' => 'method ' . $httpMethod . ' is not allowed',
                ]
            );
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            try {
                $container->call($handler, [
                    'vars' => $vars,
                ]);
            } catch (\Exception $e) {
                switch (true) {
                case ($e instanceof PostNotFound):
                    echo $forum->template->render(
                        'error.twig',
                        [
                            'title' => 'post not found',
                            'configs' => $configs,
                            'message' => 'post not found',
                        ]
                    );
                    break;
                default:
                    die($e->getMessage());
                }
            }
            break;
        }
    }

    /**
     * Produces route function for a given $_SERVER['REQUEST_URI'].
     * To route if you have web server properly setup for route file.
     *
     * @return callable A route function.
     */
    public static function routeURI()
    {
        return function () {
            // Fetch method and URI from somewhere
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $uri = $_SERVER['REQUEST_URI'];

            // Strip query; string (?foo=bar) and decode URI
            if (false !== $pos = strpos($uri, '?')) {
                $uri = substr($uri, 0, $pos);
            }
            $uri = rawurldecode($uri);

            return array($httpMethod, $uri);
        };
    }

    /**
     * Produces route function for a given QUERY_STRING.
     * A function for legacy file based routes like "/read.php?123".
     *
     * @param string|null $basename The base PHP name (e.g. "/read.php")
     * @param string|null $suffix   The part of route after query string, if any.
     *
     * @return callable A route function.
     */
    public static function routeQueryString(
        ?string $basename='/',
        ?string $suffix=null
    ) {
        return function () use ($basename, $suffix) {
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $queryString = rawurldecode($_SERVER['QUERY_STRING'] ?? '');
            $queryString = !empty($queryString) ? '/' . $queryString : '';
            $suffix = !empty($suffix) ? '/' . $suffix : '';
            return array($httpMethod, $basename . $queryString . $suffix);
        };
    }

    /**
     * Produces route function for routing against $_SERVER['PATH_INFO'].
     *
     * @return callable A route function.
     */
    public static function routePathInfo()
    {
        return function (): array {
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $pathInfo = $_SERVER['PATH_INFO'] ?? '/';
            return array($httpMethod, $pathInfo);
        };
    }

    /**
     * Serves as middle ware of route functions
     * and add a home for the '/' route
     *
     * @param string   $home  The implicit route for the path '/'.
     * @param callable $inner The inner routing callback.
     *
     * @return callable
     */
    public static function routeHome(string $home, callable $inner): callable
    {
        return function () use ($home, $inner) {
            list($httpMethod, $path) = $inner();
            $path = (rtrim($path, '/') == '') ? $home : $path;
            return array($httpMethod, $path);
        };
    }

    /**
     * Produce link of a given entity and action to it.
     *
     * @param string      $entity  The type of the entity.
     * @param string|null $id      The ID of the entity.
     * @param string|null $action  The action on the entity.
     * @param array       $options The options for rendering link.
     *
     * @return string URL link.
     */
    public function linkTo(
        string $entity,
        ?string $id=null,
        ?string $action=null,
        array $options=[]
    ): string {
        $absolute = (bool) ($options['absolute'] ?? false);
        $path = [trim($this->_basePath, '/'), $entity];

        if (!empty($id)) {
            $path[] = $id;
        }
        if (!empty($action)) {
            $path[] = $action;
        }

        // Remove empty parts from path to prevent double slashes
        $path = array_filter($path, fn($part) => !empty($part));

        // build query, if in options
        $query = '';
        $queryData = $options['query'] ?? [];
        $query = is_array($queryData) && !empty($queryData) ?
            '?' . http_build_query($queryData) : '';

        // handle exceptions
        switch ($entity) {
        case 'post':
            // saving post with no postID equals create new post
            if (empty($id) && $action === 'save') {
                array_pop($path);
                array_push($path, 'add');
            }
        }
        return ($absolute)
            ? $this->_baseURL . '/' . implode('/', $path) . $query
            : '/' . implode('/', $path) . $query;
    }

    /**
     * Gets the ForumIndex from storage.
     *
     * @return ForumIndex|null
     */
    public function getIndex(): ?ForumIndex
    {
        return $this->_storage->getIndex();
    }

    /**
     * Gets a generator of posts from newest to oldest.
     *
     * @return Generator|null
     */
    public function getPosts(): ?Generator
    {
        return $this->_storage->getPosts();
    }

    /**
     * Get the current post count from storage.
     *
     * @return integer number of post, as recorded by the storage.
     */
    public function getCount(): int
    {
        return $this->_storage->getCount();
    }

    /**
     * Increment the post count in storage.
     *
     * @return int The new count after increment.
     */
    public function incCount(): int
    {
        return $this->_storage->incCount();
    }

    /**
     * Read a certain post of the given postID
     *
     * @param string $postID ID of the post
     *
     * @return Post|null The post, or null if not found
     */
    public function readPost(string $postID): ?Post
    {
        return $this->_storage->readPost($postID);
    }

    /**
     * Write a post into storage, of the given postID.
     *
     * @param integer $postID The ID of the post.
     * @param Post    $post   The post object to store.
     *
     * @return boolean
     */
    public function writePost(int $postID, Post $post)
    {
        return $this->_storage->writePost($postID, $post);
    }

    /**
     * Append a PostSummary to the index in the storage.
     *
     * @param PostSummary $postSummary The PostSummary to store.
     * @param string|null $parentID    The ID of the parent post.
     *
     * @return boolean
     */
    public function appendIndex(
        PostSummary $postSummary,
        ?string $parentID=null
    ): bool {
        return $this->_storage->appendIndex($postSummary, $parentID);
    }

    /**
     * Gets the lock for locking down forum index / post write.
     *
     * @return Lock The lock interface for unlocking.
     */
    public function getLock(): Lock
    {
        return $this->_storage->getLock();
    }

    /**
     * Read the summary of previous post of the post of a given post ID.
     *
     * @param string $postID The postID
     *
     * @return PostSummary|null
     */
    public function readPrevPostSummary(string $postID): ?PostSummary
    {
        $prevSummary = null;
        try {
            $index = $this->_storage->getIndex();
            foreach ($index as $postSummary) {
                if ($postSummary->id == $postID) {
                    if ($prevSummary !== null) {
                        return $prevSummary;
                    }
                    throw new \Exception("post #{$postID} has no previous post");
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return null;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return null;
    }

    /**
     * Read the summary of next post of the post of a given post ID.
     *
     * @param string $postID The postID
     *
     * @return PostSummary|null
     */
    public function readNextPostSummary(string $postID): ?PostSummary
    {
        $prevSummary = null;
        try {
            $index = $this->_storage->getIndex();
            foreach ($index as $postSummary) {
                if (($prevSummary !== null) && ($prevSummary->id == $postID)) {
                    return $postSummary;
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return null;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return null;
    }

    /**
     * Read a post summary of a given post ID from storage.
     *
     * @param string $postID The postID.
     *
     * @return PostSummary|null
     */
    public function readPostSummary(string $postID): ?PostSummary
    {
        try {
            $index = $this->_storage->getIndex();
            foreach ($index as $postSummary) {
                if ($postSummary->id == $postID) {
                    return $postSummary;
                }
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return null;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return null;
    }

    /**
     * Log messages into log storage.
     *
     * @param string $msg The log message string.
     *
     * @return void
     */
    public function log(string $msg)
    {

        // get the forumName of the current user
        if (!isset($_COOKIE["forumName"])) {
            if (!isset($_COOKIE["forumCDROM"])) {
                $user = rand(0, 16384);
                setcookie("forumCDROM", $user, mktime(0, 0, 0, 1, 1, 2038), "/");
            } else {
                $user = $_COOKIE["forumCDROM"];
            }
        } else {
            $user = $_COOKIE["forumName"];
        }
        $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? '';

        // context of the request
        $context = array(
            'time' => date("D M d H:i:s Y"),
            'remoteAddr' => $_SERVER["REMOTE_ADDR"] ?? '',
            'xForwardedFor' => $_SERVER["HTTP_X_FORWARDED_FOR"] ?? '',
            'user' => $user,
            'userAgent' => $userAgent,
        );

        // do not log to storage if it is administrator
        // or is of some robot user agents.
        if ((empty($this->_administrator) || ($user != $this->_administrator))
            && !is_string(strstr($userAgent, "http://search.msn.com/msnbot.htm"))
            && !is_string(strstr($userAgent, "Free Eating Union"))
            && !is_string(strstr($userAgent, "ia_archiver"))
            && !is_string(strstr($userAgent, "sogou spider"))
            && !is_string(strstr($userAgent, "Baiduspider+(+"))
            && !is_string(strstr($userAgent, "Yahoo! Slurp"))
            && !is_string(strstr($userAgent, "Googlebot"))
        ) {
            // log the message to storage
            $this->_storage->writeLog($context, $msg);
        }
    }

}
