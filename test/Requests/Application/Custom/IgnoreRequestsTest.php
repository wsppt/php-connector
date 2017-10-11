<?php

namespace RIPS\Test\Requests\Application;

use RIPS\Test\TestCase;
use RIPS\Connector\Requests\Application\Custom\IgnoreRequests;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class IgnoreRequestsTest extends TestCase
{
    /** @var ScanRequests */
    protected $ignoreRequests;

    protected function setUp()
    {
        parent::setUp();

        $history = Middleware::history($this->container);

        $this->stack->push($history);
        $this->stack->setHandler(new MockHandler([
            new Response(200, ['x-header' => 'header-content'], '{"key": "value"}'),
        ]));

        $this->ignoreRequests = new IgnoreRequests($this->client);
    }

    /**
     * @test
     */
    public function getAll()
    {
        $response = $this->ignoreRequests->getAll(1, 2, [
            'notEqual' => [
                'phase' => 1,
            ],
            'greaterThan' => [
                'phase' => 2,
            ]
        ]);
        $request = $this->container[0]['request'];
        $queryString = urldecode($request->getUri()->getQuery());

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores', $request->getUri()->getPath());
        $this->assertEquals('value', $response->key);
        $this->assertEquals('notEqual[phase]=1&greaterThan[phase]=2', $queryString);
    }


    /**
     * @test
     */
    public function getById()
    {
        $response = $this->ignoreRequests->getById(1, 2, 3);
        $request = $this->container[0]['request'];
        $queryString = urldecode($request->getUri()->getQuery());

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores/3', $request->getUri()->getPath());
        $this->assertEquals('value', $response->key);
    }

    /**
     * @test
     */
    public function create()
    {
        $this->ignoreRequests->create(1, 2, ['test' => 'input']);
        $request = $this->container[0]['request'];
        $body =  urldecode($request->getBody()->getContents());

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores', $request->getUri()->getPath());
        $this->assertEquals('ignore[test]=input', $body);
    }

    /**
     * @test
     */
    public function update()
    {
        $this->ignoreRequests->update(1, 2, 3, ['test' => 'input']);
        $request = $this->container[0]['request'];
        $body =  urldecode($request->getBody()->getContents());

        $this->assertEquals('PATCH', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores/3', $request->getUri()->getPath());
        $this->assertEquals('ignore[test]=input', $body);
    }

    /**
     * @test
     */
    public function deleteAll()
    {
        $this->ignoreRequests->deleteAll(1, 2, [
            'notEqual' => [
                'phase' => 1,
            ],
            'greaterThan' => [
                'phase' => 2,
            ]
        ]);
        $request = $this->container[0]['request'];
        $queryString = urldecode($request->getUri()->getQuery());

        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores', $request->getUri()->getPath());
        $this->assertEquals('notEqual[phase]=1&greaterThan[phase]=2', $queryString);
    }

    /**
     * @test
     */
    public function deleteById()
    {
        $this->ignoreRequests->deleteById(1, 2, 3);
        $request = $this->container[0]['request'];
        $queryString = urldecode($request->getUri()->getQuery());

        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/applications/1/customs/2/ignores/3', $request->getUri()->getPath());
    }
}
