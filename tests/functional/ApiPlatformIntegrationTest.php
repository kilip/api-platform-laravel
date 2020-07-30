<?php

/*
 * This file is part of the Api Platform Laravel project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\ApiPlatformLaravel\Functional;

use ApiPlatformLaravel\Testing\InteractsWithORM;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\ApiPlatformLaravel\Functional\Concerns\InteractsWithUser;

class ApiPlatformIntegrationTest extends TestCase
{
    use InteractsWithORM;
    use InteractsWithUser;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->user = $this->createUser();
    }

    public function testGetCollection()
    {
        $url = route('api_users_get_collection', [], true);
        $this->assertNotNull($url);
        $this->assertEquals('http://localhost/api/users', $url);
        $response = $this->getJson($url);

        if (@$response->exception) {
            throw $response->exception;
        }
        $response->assertOk();
        $json = $response->json();
        $this->assertCount(1, $json);
    }

    public function testPostCollection()
    {
        $user = [
            'username' => 'post_collection',
            'email' => 'test@putcollection.com',
            'password' => Hash::make('test'),
            'fullname' => 'Test Put Collection',
        ];

        $url = route('api_users_post_collection');
        $response = $this->postJson($url, $user);
        $this->assertResponseNoException($response);
        $response->assertStatus(Response::HTTP_CREATED);
        $json = $response->json();
        $this->assertNotNull($response->json('id'));
        $this->assertEquals($user['fullname'], $response->json('fullname'));
    }

    public function testGetItem()
    {
        $user = $this->createUser();
        $uri = route('api_users_get_item', ['id' => $user->getId()]);

        $response = $this->getJson($uri);
        $this->assertResponseNoException($response);
        $response->assertOk();

        $this->assertEquals('test', $response->json('username'));
    }

    public function testPutItem()
    {
        $user = $this->createUser();
        $uri = route('api_users_put_item', ['id' => $user->getId()]);
        $response = $this->putJson($uri, [
            'fullname' => 'Test Update User',
        ]);
        $this->assertResponseNoException($response);
        $response->assertOk();
        $this->assertEquals('Test Update User', $response->json('fullname'));
    }

    protected function assertResponseNoException($response)
    {
        if (@$response->exception) {
            throw $response->exception;
        }
    }
}
