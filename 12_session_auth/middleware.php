<?php

abstract class Middleware
{
    abstract public function handle(callable $next): mixed;

    public static function pipe(array $middlewares, callable $final): callable
    {
        $pipeline = $final;

        foreach (array_reverse($middlewares) as $middleware) {
            $next = $pipeline;
            $pipeline = function () use ($middleware, $next) {
                return $middleware->handle($next);
            };
        }

        return $pipeline;
    }
}

class AuthMiddleware extends Middleware
{
    private string $redirect;

    public function __construct(string $redirect = '/login')
    {
        $this->redirect = $redirect;
    }

    public function handle(callable $next): mixed
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu');
            echo "[AuthMiddleware] Redirect ke {$this->redirect}\n";
            return null;
        }

        echo "[AuthMiddleware] Authenticated as: " . (Auth::user()['name'] ?? 'Unknown') . "\n";
        return $next();
    }
}

class RoleMiddleware extends Middleware
{
    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function handle(callable $next): mixed
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login');
            return null;
        }

        $required = explode('|', $this->role);
        $userRole = Auth::role();

        if (!in_array($userRole, $required)) {
            Session::flash('error', "Akses ditolak. Butuh role: {$this->role}");
            echo "[RoleMiddleware] Access denied for role: $userRole\n";
            return null;
        }

        echo "[RoleMiddleware] Role $userRole granted access\n";
        return $next();
    }
}

class ThrottleMiddleware extends Middleware
{
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 5, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(callable $next): mixed
    {
        $key = '_throttle_' . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $attempts = Session::get($key, []);

        $this->cleanExpired($attempts);

        if (count($attempts) >= $this->maxAttempts) {
            echo "[ThrottleMiddleware] Too many attempts. Maximum: {$this->maxAttempts}\n";
            return null;
        }

        $attempts[] = time();
        Session::set($key, $attempts);

        return $next();
    }

    private function cleanExpired(array &$attempts): void
    {
        $expire = time() - ($this->decayMinutes * 60);
        $attempts = array_filter($attempts, fn($time) => $time > $expire);
    }
}

class LogMiddleware extends Middleware
{
    private string $label;

    public function __construct(string $label = 'Request')
    {
        $this->label = $label;
    }

    public function handle(callable $next): mixed
    {
        $start = microtime(true);
        echo "[LogMiddleware] {$this->label} started\n";

        $result = $next();

        $duration = (microtime(true) - $start) * 1000;
        echo "[LogMiddleware] {$this->label} completed in " . round($duration, 2) . "ms\n";

        return $result;
    }
}

class CsrfMiddleware extends Middleware
{
    public function handle(callable $next): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!Session::verifyToken($token)) {
                echo "[CsrfMiddleware] CSRF token mismatch\n";
                return null;
            }
            echo "[CsrfMiddleware] CSRF token verified\n";
        }
        return $next();
    }
}
