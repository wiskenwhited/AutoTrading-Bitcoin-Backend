<?php

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}
if (! function_exists('web_url')) {
    /**
     * Get Web Url
     *
     * @param  string $path
     * @return string
     */
    function web_url($path = '')
    {
        $host = 'xchangerate.io';
        if (env('API_ENV') == 'staging') {
            $host  = 'beta.' . $host;
        }
        return env('APP_WEB_URL', 'http://' . $host) . $path;
    }
}

/**
 * Return a new response from the application.
 *
 * @param  string  $content
 * @param  int     $status
 * @param  array   $headers
 * @return \Illuminate\Http\Response|ResponseFactory
 */
function response($content = '', $status = 200, array $headers = [])
{
    $factory = new App\Http\ResponseFactory;

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($content, $status, $headers);
}