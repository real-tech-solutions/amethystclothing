<?php
/**
 * Minimal clean-URL router for Amethystclothing.lk
 * Supports {param} placeholders and wraps views in the master layout.
 */

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $pattern, string $view): self
    {
        $this->routes['GET'][] = ['pattern' => $pattern, 'handler' => $view];
        return $this;
    }

    public function post(string $pattern, string $handler): self
    {
        $this->routes['POST'][] = ['pattern' => $pattern, 'handler' => $handler];
        return $this;
    }

    public function resolve(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->match($route['pattern'], $uri);
            if ($params !== false) {
                $handlerFile = $route['handler'];

                if (!file_exists($handlerFile)) {
                    $this->send404();
                    return;
                }

                if ($method === 'POST') {
                    extract($params);
                    include $handlerFile;
                    return;
                }

                // GET: render view inside master layout
                extract($params);
                ob_start();
                include $handlerFile;
                $content = ob_get_clean();

                // Standalone pages (like printable invoice or admin login) bypass master layout
                if (!empty($standalone) || strpos($handlerFile, 'invoice.php') !== false) {
                    echo $content;
                    return;
                }

                // $pageTitle and $pageDescription may have been set by the view
                $pageTitle       = $pageTitle ?? null;
                $pageDescription = $pageDescription ?? null;

                // Admin pages wrap in dedicated admin layout
                $isAdminPath = strpos($handlerFile, '/admin/') !== false || strpos($handlerFile, '\\admin\\') !== false;
                if (!empty($isAdmin) || $isAdminPath) {
                    include __DIR__ . '/../views/layouts/admin_master.php';
                    return;
                }

                include __DIR__ . '/../views/layouts/master.php';
                return;
            }
        }

        $this->send404();
    }

    /**
     * Match a URI against a route pattern.
     * Returns an associative array of extracted params, or false.
     */
    private function match(string $pattern, string $uri): array|false
    {
        // Exact match (no params)
        if ($pattern === $uri) return [];

        // Convert {param} to named regex groups
        $paramNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_]+)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        if (empty($paramNames)) return false;

        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $uri, $matches)) {
            $params = [];
            foreach ($paramNames as $i => $name) {
                $params[$name] = urldecode($matches[$i + 1]);
            }
            return $params;
        }

        return false;
    }

    private function send404(): void
    {
        http_response_code(404);
        $content = '<section style="text-align:center;padding:120px 20px;">'
            . '<h1 style="font-family:Archivo Black,sans-serif;font-size:72px;color:#0D0B12;">404</h1>'
            . '<p style="font-family:Archivo Narrow,sans-serif;font-size:16px;color:#5A5565;text-transform:uppercase;letter-spacing:0.08em;">Page not found</p>'
            . '<a href="' . base_url() . '" style="display:inline-block;margin-top:32px;padding:14px 40px;background:#0B0910;color:#fff;font-family:Archivo Narrow,sans-serif;text-transform:uppercase;letter-spacing:0.08em;text-decoration:none;font-size:13px;">Back to Home</a>'
            . '</section>';
        $pageTitle = '404 — Not Found';
        if (file_exists(__DIR__ . '/../views/layouts/master.php')) {
            include __DIR__ . '/../views/layouts/master.php';
        } else {
            echo $content;
        }
    }
}
