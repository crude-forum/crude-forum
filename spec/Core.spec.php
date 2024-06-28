<?php

use CrudeForum\CrudeForum\Core;
use CrudeForum\CrudeForum\Config;
use CrudeForum\CrudeForum\ForumIndex;
use CrudeForum\CrudeForum\Lock;
use CrudeForum\CrudeForum\Post;
use CrudeForum\CrudeForum\PostSummary;
use CrudeForum\CrudeForum\Storage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

describe(Core::class, function () {
    describe('::linkTo', function () {

        $createTestEnv = function (string $baseURL, string $basePath): Core {
            $dummyStorage = new class implements Storage {
                public function getIndex(): ?ForumIndex {
                    return null;
                }
                public function getPosts(): \Generator {
                    yield true;
                }
                public function getCount(): int {
                    return 0;
                }
                public function incCount(): int {
                    return 0;
                }
                public function readPost(string $postID): ?Post {
                    return null;
                }
                public function writePost(int $postID, Post $post): bool {
                    return false;
                }
                public function appendIndex(PostSummary $postSummary, ?string $parentID = null): bool
                {
                    return false;
                }
                public function getLock(): Lock {
                    return new Lock();
                }
                public function writeLog(array $context, string $msg)
                {
                }
            };
            $dummyTwig = new Environment(new ArrayLoader());
            $configs = new Config(
                baseURL: $baseURL,
                basePath: $basePath,
            );

            // Create forum
            return new Core(
                $dummyStorage,
                $dummyTwig,
                $configs,
            );
        };

        it('should handle links when basePath is root', function () use ($createTestEnv) {
            $forum = $createTestEnv(
                baseURL: 'http://example.com',
                basePath: '/',
            );
            expect($forum->linkTo('post', 1, 'view'))
                ->toBe('/post/1/view');
            expect($forum->linkTo('post', 1, 'view', ['absolute' => true]))
                ->toBe('http://example.com/post/1/view');

            $forum = $createTestEnv(
                baseURL: 'http://example.com/',
                basePath: '/',
            );
            expect($forum->linkTo('post', 1, 'view'))
                ->toBe('/post/1/view');
            expect($forum->linkTo('post', 1, 'view', ['absolute' => true]))
                ->toBe('http://example.com/post/1/view');

        });

        it('should handle links when basePath is not root', function () use ($createTestEnv) {
            $forum = $createTestEnv(
                baseURL: 'http://example.com',
                basePath: '/forum',
            );
            expect($forum->linkTo('post', 1, 'view'))
                ->toBe('/forum/post/1/view');
            expect($forum->linkTo('post', 1, 'view', ['absolute' => true]))
                ->toBe('http://example.com/forum/post/1/view');

            $forum = $createTestEnv(
                baseURL: 'http://example.com/',
                basePath: '/forum',
            );
            expect($forum->linkTo('post', 1, 'view'))
                ->toBe('/forum/post/1/view');
            expect($forum->linkTo('post', 1, 'view', ['absolute' => true]))
                ->toBe('http://example.com/forum/post/1/view');

            $forum = $createTestEnv(
                baseURL: 'http://example.com/',
                basePath: '/forum/',
            );
            expect($forum->linkTo('post', 1, 'view'))
                ->toBe('/forum/post/1/view');
            expect($forum->linkTo('post', 1, 'view', ['absolute' => true]))
                ->toBe('http://example.com/forum/post/1/view');
        });
    });
});