<?php

return [
    'index_page_length' => env('POST_INDEX_PAGE_LENGTH', 10), // how many posts should appear per page
    'summary_length'    => env('POST_SUMMARY_LENGTH', 140), // how many character to show before truncation on index page
];
