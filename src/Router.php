<?php
/**
 * Router - Roteador simples baseado em array de rotas.
 * Mapeia URI + método HTTP para funções handler.
 */
class Router
{
    private array $routes = [];
    private ?string $basePath;

    public function __construct(string $basePath = '')
    {
        // Remove barra do final e normaliza
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Registra uma rota GET.
     */
    public function get(string $path, callable $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    /**
     * Registra uma rota POST.
     */
    public function post(string $path, callable $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    /**
     * Processa a requisição atual e executa o handler correspondente.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path do URI
        $uri = rawurldecode($uri);
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) continue;

            $params = $this->match($routePath, $uri);
            if ($params !== null) {
                call_user_func_array($handler, $params);
                return;
            }
        }

        // Rota não encontrada
        http_response_code(404);
        include dirname(__DIR__) . '/templates/errors/404.php';
    }

    /**
     * Compara o padrão de rota com o URI e extrai parâmetros nomeados.
     * Ex: /user/{id} corresponde a /user/42 e retorna ['42'].
     * Retorna null se não corresponder.
     */
    private function match(string $routePath, string $uri): ?array
    {
        // Rotas estáticas - comparação direta
        if (!str_contains($routePath, '{')) {
            return ($routePath === $uri) ? [] : null;
        }

        // Converter {param} para regex
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // remove match completo
            return $matches;
        }

        return null;
    }
}
