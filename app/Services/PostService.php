<?php


namespace App\Services;


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
//        return \Cache::remember('posts-index', config('posts.index_ttl'), function () {
            $files = glob(base_path('content/posts/*.md'));
            $posts = collect([]);

            foreach ($files as $file) {
                $posts[] = $this->getPostMeta(pathinfo($file, PATHINFO_BASENAME));
            }

            return $posts->sortByDesc('published_at');
//        });
    }

    public function getPostMeta($filename)
    {
        $postMatter = YamlFrontMatter::parseFile(base_path('content/posts/' . $filename));
        $post       = $postMatter->matter();
        $slug       = $postMatter->matter('slug') ?: str_slug(explode('.', pathinfo($filename, PATHINFO_FILENAME))[1]);

        $post['path']  = $filename;
        $post['slug']  = $slug;
        $post['month'] = date('m', $post['published_at']);
        $post['year']  = date('Y', $post['published_at']);
        $post['url']   = route('page.posts/show', [
            'year'  => $post['year'],
            'month' => $post['month'],
            'slug'  => $slug,
        ]);

        $newTags = [];
        foreach ($post['tags'] as $tag) {
            $tagSlug   = str_slug($tag);
            $newTags[$tagSlug] = [
                'tag'  => $tag,
                'slug' => $tagSlug,
                'url'  => route('page.posts/tags/show', ['tag' => $tagSlug]),
            ];
        }
        $post['tags'] = $newTags;

        return $post;
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

        if ($post) {
            $postMatter   = YamlFrontMatter::parseFile(base_path('content/posts/' . $post['path']));
            $converter    = new CommonMarkConverter();
            $post['body'] = $converter->convertToHtml($postMatter->body());
        }

        return $post ?: null;
    }

}
