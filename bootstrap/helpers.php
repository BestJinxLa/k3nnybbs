<?php

if (! function_exists('route_class')) {
    /**
     * get html class for route.
     *
     *
     */
    function route_class()
    {
        return str_replace('.', '-', Route::currentRouteName());
    }

    /**
     * 录由文章内容中自动生成
     *
     *
     */
    function make_excerpt($value, $length = 200)
    {
        $excerpt = trim(preg_replace('/\r\n|\r|\n+/', ' ', strip_tags($value)));
        return str_limit($excerpt, $length);
    }
}

?>