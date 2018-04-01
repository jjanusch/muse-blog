<?php


namespace App\Http\Controllers;


use App\Services\PostService;

class PostController extends Controller
{
    public function index(int $page = 1)
    {
        $postService = new PostService();
        $posts       = $postService->getPosts();

        $startIndex = ($page - 1) * config('posts.index_page_length');

        if ($page < 1 || $startIndex < 0 || $startIndex >= $posts->count()) {
            abort(404);
        }

        return view('pages.posts.index', [
            'posts'      => $posts->slice($startIndex, config('posts.index_page_length')),
            'pagination' => [
                'total'    => $posts->count(),
                'previous' => $page > 1 ? route('page.posts', ['page' => $page - 1]) : null,
                'next'     => $page < floor($posts->count() / config('posts.index_page_length')) ?
                    route('page.posts', [$page + 1])
                    : null,
            ],
        ]);
    }

    public function show($year, $month, $slug)
    {
        $postService = new PostService();
        $post        = $postService->getPost($year, $month, $slug);

        if (!$post) {
            abort(404);
        }

        return view('pages.posts.show', [
            'post' => $post,
        ]);
    }


    public function tag($tagSlug, int $page = 1)
    {
        $postService = new PostService();
        $posts       = $postService->getPosts()->filter(function ($post) use ($tagSlug) {
            return in_array($tagSlug, array_pluck($post['tags'], 'tag'));
        });

        $startIndex = ($page - 1) * config('posts.index_page_length');

        if ($page < 1 || $startIndex < 0 || $startIndex > $posts->count()) {
            abort(404);
        }


        return view('pages.posts.index', [
            'tag_slug'   => $tagSlug,
            'posts'      => $posts->slice($startIndex, config('posts.index_page_length')),
            'pagination' => [
                'total'    => $posts->count(),
                'previous' => $page > 1 ? route('page.posts/tags/show', ['page' => $page - 1]) : null,
                'next'     => $page <= floor($posts->count() / config('posts.index_page_length')) ?
                    route('page.posts/tags/show', [$page + 1])
                    : null,
            ],
        ]);
    }
}
