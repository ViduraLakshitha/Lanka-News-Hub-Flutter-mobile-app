<?php
namespace App\Core;

class Cache {
    private static ?Cache $instance = null;
    private string $driver;
    private string $cachePath;
    private ?\Redis $redis = null;

    private function __construct() {
        $this->driver = strtolower(defined('CACHE_DRIVER') ? CACHE_DRIVER : ($_ENV['CACHE_DRIVER'] ?? 'none'));
        $this->cachePath = dirname(__DIR__, 2) . '/storage/cache';
        
        if ($this->driver === 'file' && !is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0700, true);
        }

        if ($this->driver === 'redis' && class_exists('Redis')) {
            try {
                $redis = new \Redis();
                $host = defined('REDIS_HOST') ? REDIS_HOST : ($_ENV['REDIS_HOST'] ?? '127.0.0.1');
                $port = (int)(defined('REDIS_PORT') ? REDIS_PORT : ($_ENV['REDIS_PORT'] ?? 6379));
                $pass = defined('REDIS_PASSWORD') ? REDIS_PASSWORD : ($_ENV['REDIS_PASSWORD'] ?? '');
                
                if ($redis->connect($host, $port, 1.5)) {
                    if ($pass !== '') {
                        $redis->auth($pass);
                    }
                    $this->redis = $redis;
                } else {
                    $this->driver = 'file';
                }
            } catch (\Throwable $e) {
                // Fail gracefully to file cache
                $this->driver = 'file';
            }
        }
    }

    public static function getInstance(): Cache {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get(string $key, $default = null) {
        return self::getInstance()->fetch($key, $default);
    }

    public static function set(string $key, $value, int $ttl = 300): bool {
        return self::getInstance()->store($key, $value, $ttl);
    }

    public static function delete(string $key): bool {
        return self::getInstance()->remove($key);
    }

    public static function flush(): bool {
        return self::getInstance()->clear();
    }

    public static function remember(string $key, int $ttl, callable $callback) {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public function fetch(string $key, $default = null) {
        if ($this->driver === 'none') {
            return $default;
        }

        if ($this->driver === 'redis' && $this->redis) {
            try {
                $val = $this->redis->get('news:' . $key);
                return $val !== false ? unserialize($val) : $default;
            } catch (\Throwable) {
                return $default;
            }
        }

        $file = $this->cachePath . '/' . hash('sha256', $key) . '.cache';
        if (!is_file($file)) {
            return $default;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $data = @unserialize($content);
        if (!is_array($data) || !isset($data['expires_at']) || !array_key_exists('value', $data)) {
            @unlink($file);
            return $default;
        }

        if (time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    public function store(string $key, $value, int $ttl = 300): bool {
        if ($this->driver === 'none') {
            return true;
        }

        if ($this->driver === 'redis' && $this->redis) {
            try {
                return $this->redis->setex('news:' . $key, $ttl, serialize($value));
            } catch (\Throwable) {
                return false;
            }
        }

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0700, true);
        }

        $file = $this->cachePath . '/' . hash('sha256', $key) . '.cache';
        $payload = serialize([
            'expires_at' => time() + $ttl,
            'value' => $value
        ]);

        $tmpFile = $file . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (@file_put_contents($tmpFile, $payload, LOCK_EX) !== false) {
            return @rename($tmpFile, $file);
        }
        return false;
    }

    public function remove(string $key): bool {
        if ($this->driver === 'none') {
            return true;
        }

        if ($this->driver === 'redis' && $this->redis) {
            try {
                return (bool)$this->redis->del('news:' . $key);
            } catch (\Throwable) {
                return false;
            }
        }

        $file = $this->cachePath . '/' . hash('sha256', $key) . '.cache';
        if (is_file($file)) {
            return @unlink($file);
        }
        return true;
    }

    public function clear(): bool {
        if ($this->driver === 'none') {
            return true;
        }

        if ($this->driver === 'redis' && $this->redis) {
            try {
                $keys = $this->redis->keys('news:*');
                if (!empty($keys)) {
                    $this->redis->del($keys);
                }
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if (is_dir($this->cachePath)) {
            foreach (glob($this->cachePath . '/*.cache') as $file) {
                @unlink($file);
            }
        }
        return true;
    }
}
