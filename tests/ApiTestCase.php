<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ApiTestCase extends TestCase
{
    use DatabaseMigrations;

    protected function getAuthToken(User $user = null)
    {
        $user = $user ?: factory(User::class)->create();

        $this->json('POST', 'api/auth/login', [
            'email' => $user->email,
            'password' => 'fakepassword'
        ], $this->getHeaders());
        $response = json_decode($this->response->getContent());

        return object_get($response, 'data.api_key');
    }

    protected function getHeaders($headers = [])
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ], $headers);
    }

    protected function getHeadersWithAuthToken($headers = [], User $user = null)
    {
        $token = $this->getAuthToken($user);
        $headers['Authorization'] = "Bearer $token";

        return $this->getHeaders($headers);
    }

    protected function authenticatedJson($method, $uri, $data = [], $headers = [], User $user = null)
    {
        return $this->json($method, $uri, $data, $this->getHeadersWithAuthToken($headers, $user));
    }
}