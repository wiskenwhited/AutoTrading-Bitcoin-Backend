<?php

trait UsesMockHttpClientTrait
{
    protected function mockHttpClient(array $expectedRequests)
    {
        $httpClient = Mockery::mock(\GuzzleHttp\Client::class);
        foreach ($expectedRequests as $request) {
            $expectation = $httpClient->shouldReceive('request');
            $expectation->once();
            if (array_key_exists('expected_data', $request)) {
                $expectation->with(
                    $request['expected_method'],
                    $request['expected_uri'],
                    $request['expected_data'] ? ['json' => $request['expected_data']] : []
                );
            } else {
                $expectation->with(
                    $request['expected_method'],
                    $request['expected_uri']
                );
            }
            $expectation->andReturn($this->mockHttpClientJsonResponse($request['expected_response']));
        }

        return $httpClient;
    }

    protected function mockHttpClientJsonResponse(array $data)
    {
        $body = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
        $body->shouldReceive('getContents')
            ->andReturn(json_encode($data));
        $response = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
        $response->shouldReceive('getBody')
            ->andReturn($body);

        return $response;
    }
}