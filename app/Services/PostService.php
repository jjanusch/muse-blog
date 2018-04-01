<?php


namespace App\Services;


use Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use League\CommonMark\CommonMarkConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class PostService
{
    /**
     * Returns and caches an Collection contianing the meta data for each available post,
     * sorted in reverse chronilogical order
     *
     * Clear Laravel cache to update post cache
     *
     * @return Collection
     */
    public function getPosts()
    {
        return Cache::rememberForever('posts.all', function () {
            $markdownConverter = new CommonMarkConverter();

            return collect(glob(base_path('content/posts/*.md')))->map(function ($filename) use ($markdownConverter) {
                $document = YamlFrontMatter::parseFile($filename);
                $post     = $document->matter();
                $slug     = $document->matter('slug') ?: str_slug(explode('.', pathinfo($filename, PATHINFO_FILENAME))[1]);
                $year     = date('Y', $post['published_at']);
                $month    = date('m', $post['published_at']);

                return [
                    'path'         => $filename,
                    'slug'         => $slug,
                    'year'         => $year,
                    'month'        => $month,
                    'url'          => route('page.posts/show', [
                        'year'  => $year,
                        'month' => $month,
                        'slug'  => $slug,
                    ]),
                    'title'        => $document->title,
                    'summary'      => $document->summary,
                    'published_at' => Carbon::createFromFormat('U', $document->published_at),
                    'body'         => $markdownConverter->convertToHtml($document->body()),
                    'tags'         => isset($post['tags']) ? (is_array($post['tags']) ? array_map(function ($tag) {
                        $tagSlug = str_slug($tag);

                        return [
                            'tag'  => $tag,
                            'slug' => $tagSlug,
                            'url'  => route('page.posts/tags/show', ['tag' => $tagSlug]),
                        ];
                    }, $post['tags']) : (array)$post['tags']) : [],
                ];
            })->sortByDesc('published_at');
        });
    }

    /**
     * Retrieves a post's metadata and the formatted body of the post when searching via month, year, and slug
     *
     * @param $month
     * @param $year
     * @param $slug
     *
     * @return null
     */
    public function getPost($year, $month, $slug)
    {
        $postService = new PostService();
        $posts       = $postService->getPosts();
        $post        = $posts->where('year', $year)->where('month', $month)->where('slug', $slug)->first();

        return $post ?: null;
    }

}
