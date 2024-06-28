<?php

namespace CrudeForum\CrudeForum;

class Config
{
    /**
     * Constructor
     *
     * @param string $siteName
     *      Site name
     * @param string $sloganTop
     *      Slogan on top of the site name
     * @param string $sloganBottom
     *      Slogan on bottom of the site name
     * @param string $baseURL
     *      Base URL of the forum
     * @param string $basePath
     *      Base URL path of the forum frontpage, under the $baseURL
     * @param string $assetsPath
     *      Base URL path to the assets folder
     * @param int $postPerPage
     *      Number of post per page in the forum
     * @param string $timezone
     *      Timezone of the forum
     * @param int $rssPostLimit
     *      Number of post to show in the RSS feed
     * @param string $formNamePostAuthor
     *      The HTML field name and id of the author field in post form
     * @param string $formNamePostTitle
     *      The HTML field name and id of the title field in post form
     * @param string $formNamePostBody
     *      The HTML field name and id of the body field in post form
     * @param bool $debug
     *      Flag if the forum is in debug mode
     *
     * @return void
     */
    public function __construct(
        public string $siteName = 'CrudeForum',
        public string $sloganTop = 'A simple forum',
        public string $sloganBottom = 'Powered by CrudeForum',
        public string $baseURL = '',
        public string $basePath = '/',
        public string $assetsPath = '/assets',
        public int $postPerPage = 100,
        public string $timezone = 'UTC',
        public int $rssPostLimit = 10,
        public string $formNamePostAuthor = '',
        public string $formNamePostTitle = '',
        public string $formNamePostBody = '',
        public bool $debug = false,
    ) {
        $this->baseURL = rtrim($this->baseURL, '/');
    }
}
