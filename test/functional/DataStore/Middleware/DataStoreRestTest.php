<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\functional\DataStore\Middleware;

use PHPUnit\Framework\TestCase;
use rollun\datastore\DataStore\Interfaces\DataStoreInterface;
use rollun\datastore\Middleware\DataStoreRest;
use rollun\datastore\Middleware\Handler;
use SplQueue;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\Route;

class DataStoreRestTest extends TestCase
{
    public function testConstruct()
    {
        /** @var DataStoreInterface| $dataStoreMock */
        $dataStoreMock = $this->getMockBuilder(DataStoreInterface::class)->getMock();

        $middlewarePipe = new MiddlewarePipe();
        $middlewarePipe->pipe(new Handler\QueryHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\ReadHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\CreateHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\UpdateHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\RefreshHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\DeleteHandler($dataStoreMock));
        $middlewarePipe->pipe(new Handler\ErrorHandler());

        $this->assertAttributeEquals($middlewarePipe, 'middlewarePipe', new DataStoreRest($dataStoreMock));
    }
}
