<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 08.08.17.
 * Time: 12:27
 */

namespace App\Http;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;

class ResponseFactory extends \Laravel\Lumen\Http\ResponseFactory
{
    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Illuminate\Http\JsonResponse;
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        // If response status is a 4xx or 5xx status code, mark request as failed
        $requestFailed = in_array(floor($status / 100), [4, 5]);
        if ($requestFailed) {
            $message = $data;
            if (array_get($data, 'message')) {
                $message = array_get($data, 'message');
            }
            $data = ['error' => $message];
        } elseif (! $data) {
            $data = ['data' => []];
        } elseif (! is_array($data) || ! array_key_exists('data', $data)) {
            $data = ['data' => $data];
        }
        $data['status'] = $requestFailed ? 'failed' : 'ok';

        return new JsonResponse($data, $status, $headers, $options);
    }
}