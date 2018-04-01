<?php


namespace App\Http\Controllers;


use League\CommonMark\CommonMarkConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class PageController extends Controller
{
    public function show($slug)
    {
        $filename = base_path('content/pages/' . $slug . '.md');

        if (!file_exists($filename)) {
            abort(404);
        }

        $pageMatter   = YamlFrontMatter::parseFile($filename);
        $converter    = new CommonMarkConverter();
        $page = $pageMatter->matter();
        $page['body'] = $converter->convertToHtml($pageMatter->body());

        return view('pages.page', ['page' => $page]);
    }
}
