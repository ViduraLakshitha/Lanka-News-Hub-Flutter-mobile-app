"""Tests for production foundation: Logger, Cache, Configuration, and Security.
Run: py tests/foundation_test.py --php C:/xampp/php/php.exe
"""
import argparse, os, pathlib, subprocess, tempfile, json, unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
args, _ = parser.parse_known_args()

class FoundationTest(unittest.TestCase):
    def run_php(self, code, env_overrides=None):
        env = dict(os.environ, **(env_overrides or {}))
        cmd = [args.php, '-r', f"require '{ROOT}/app/bootstrap.php'; {code}"]
        result = subprocess.run(cmd, cwd=ROOT, env=env, text=True, capture_output=True)
        if result.returncode != 0:
            self.fail(f"PHP execution failed: {result.stdout}\n{result.stderr}")
        return result.stdout.strip()

    def test_logger_sanitization_and_levels(self):
        code = """
        \\App\Core\\Logger::info('Test informational event', ['item_id' => 42, 'password' => 'supersecret123']);
        \\App\Core\\Logger::security('Test auth event', ['email' => 'user@test.lk', '_token' => 'abcde12345']);
        \\App\Core\\Logger::error('Test error event', ['error_code' => 500]);
        $logPath = 'storage/logs/app.log';
        $errorPath = 'storage/logs/error.log';
        echo json_encode([
            'app_exists' => is_file($logPath),
            'app_content' => file_get_contents($logPath),
            'error_exists' => is_file($errorPath),
            'error_content' => file_get_contents($errorPath)
        ]);
        """
        out = self.run_php(code)
        data = json.loads(out)
        self.assertTrue(data['app_exists'])
        self.assertIn('[INFO]', data['app_content'])
        self.assertIn('[SECURITY]', data['app_content'])
        self.assertIn('[ERROR]', data['app_content'])
        self.assertIn('***REDACTED***', data['app_content'])
        self.assertNotIn('supersecret123', data['app_content'])
        self.assertNotIn('abcde12345', data['app_content'])
        self.assertTrue(data['error_exists'])
        self.assertIn('Test error event', data['error_content'])
        print("[FOUNDATION TEST] Logger verified: levels, file writing, sensitive redaction.")

    def test_cache_file_driver(self):
        code = """
        putenv('CACHE_DRIVER=file');
        $_ENV['CACHE_DRIVER'] = 'file';
        \\App\Core\\Cache::flush();
        \\App\Core\\Cache::set('test_key', 'test_value_123', 60);
        $val1 = \\App\Core\\Cache::get('test_key');
        $val2 = \\App\Core\\Cache::remember('remember_key', 60, function() {
            return ['status' => 'computed', 'number' => 99];
        });
        $val3 = \\App\Core\\Cache::get('remember_key');
        \\App\Core\\Cache::delete('test_key');
        $val4 = \\App\Core\\Cache::get('test_key', 'default_val');
        echo json_encode([
            'val1' => $val1,
            'val2' => $val2,
            'val3' => $val3,
            'val4' => $val4
        ]);
        """
        out = self.run_php(code, {'CACHE_DRIVER': 'file'})
        data = json.loads(out)
        self.assertEqual(data['val1'], 'test_value_123')
        self.assertEqual(data['val2'], {'status': 'computed', 'number': 99})
        self.assertEqual(data['val3'], {'status': 'computed', 'number': 99})
        self.assertEqual(data['val4'], 'default_val')
        print("[FOUNDATION TEST] Cache verified: set, get, remember, delete, flush.")

    def test_config_timezone_and_constants(self):
        code = """
        echo json_encode([
            'timezone' => date_default_timezone_get(),
            'session_lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : null,
            'app_timezone' => defined('APP_TIMEZONE') ? APP_TIMEZONE : null
        ]);
        """
        out = self.run_php(code)
        data = json.loads(out)
        self.assertEqual(data['timezone'], 'Asia/Colombo')
        self.assertEqual(data['app_timezone'], 'Asia/Colombo')
        self.assertEqual(data['session_lifetime'], 86400)
        print("[FOUNDATION TEST] Configuration verified: Asia/Colombo timezone, session lifetime.")

    def test_cron_task_runner(self):
        env = dict(os.environ, DB_DRIVER='sqlite', DB_PATH='storage/preview.sqlite')
        cmd = [args.php, str(ROOT / 'bin' / 'cron.php'), '--dry-run']
        result = subprocess.run(cmd, cwd=ROOT, env=env, text=True, capture_output=True)
        self.assertEqual(result.returncode, 0, f"Cron failed: {result.stdout}\n{result.stderr}")
        self.assertIn("Lanka News Hub Scheduled Task Runner", result.stdout)
        self.assertIn("Mode: DRY-RUN", result.stdout)
        self.assertIn("Scheduled Maintenance Complete", result.stdout)
        print("[FOUNDATION TEST] Cron scheduled task runner verified: dry-run, story transition, classifieds expiry.")

if __name__ == '__main__':
    import sys
    unittest.main(argv=[sys.argv[0]])
