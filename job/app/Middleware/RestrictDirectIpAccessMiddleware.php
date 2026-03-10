<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class RestrictDirectIpAccessMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. 如果环境变量 ALLOW_DIRECT_IP_ACCESS 为 true，则直接放行
        // 注意：这里读取的是容器环境变量，由 docker-compose 传递
        // Hyperf 中可以使用 env() 函数
        $allowDirectIp = env('ALLOW_DIRECT_IP_ACCESS', true);
        if (filter_var($allowDirectIp, FILTER_VALIDATE_BOOLEAN)) {
            return $handler->handle($request);
        }

        // 2. 获取请求 Host
        $host = $request->getUri()->getHost();
        
        // 3. 获取配置的 APP_URL
        // 这里我们优先读取 JOB_URL，如果没有则尝试 APP_URL
        $jobUrl = env('JOB_URL', env('APP_URL', 'https://job.aloure-web.top'));
        
        // 提取域名部分
        $jobHost = parse_url($jobUrl, PHP_URL_HOST);
        // 如果 parse_url 失败（例如没有 scheme），则直接把整个 url 当作 host
        if (!$jobHost) {
             $jobHost = $jobUrl;
        }

        // 4. 允许本地回环
        if ($host === '127.0.0.1' || $host === 'localhost') {
            return $handler->handle($request);
        }

        // 5. 核心逻辑：校验 Host
        // 如果配置了域名，且请求 Host 不匹配，则拒绝
        if ($jobHost && $host !== $jobHost) {
             // 使用 Hyperf 的 Response 对象返回 403
             $response = $this->container->get(HttpResponse::class);
             return $response->raw('Forbidden: Direct IP access is not allowed.')->withStatus(403);
        }

        return $handler->handle($request);
    }
}
