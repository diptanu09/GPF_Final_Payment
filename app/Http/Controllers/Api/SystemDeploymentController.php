<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemDeploymentController extends Controller
{
    /**
     * Get deployment security token from config/env
     */
    protected function getExpectedToken(): string
    {
        return config('app.deploy_token', env('DEPLOY_TOKEN', 'GPF_DEPLOY_SECRET_TOKEN_2026'));
    }

    /**
     * Validate deployment token
     */
    protected function authorizeRequest(Request $request): ?JsonResponse
    {
        $providedToken = $request->header('X-Deploy-Token') ?: $request->input('deploy_token');
        $expectedToken = $this->getExpectedToken();

        if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            Log::warning("Unauthorized remote deployment attempt from IP: " . $request->ip());
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid or missing X-Deploy-Token header.',
            ], 403);
        }

        return null;
    }

    /**
     * Get live deployment and system status
     */
    public function status(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $gitBranch = 'unknown';
        $gitCommit = 'unknown';
        $gitCommitDate = 'unknown';

        if (file_exists(base_path('.git'))) {
            $gitBranch = trim((string) @shell_exec('git rev-parse --abbrev-ref HEAD 2>&1')) ?: 'unknown';
            $gitCommit = trim((string) @shell_exec('git rev-parse --short HEAD 2>&1')) ?: 'unknown';
            $gitCommitDate = trim((string) @shell_exec('git log -1 --format=%cd --date=iso 2>&1')) ?: 'unknown';
        }

        $dbStatus = 'disconnected';
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected (' . DB::connection()->getDriverName() . ')';
        } catch (\Throwable $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        $oracleBridge = app(\App\Services\Integration\OracleMasterBridge::class);
        $oracleStatus = $oracleBridge->getConnectionStatus();

        return response()->json([
            'success' => true,
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'git_branch' => $gitBranch,
            'git_commit' => $gitCommit,
            'git_commit_date' => $gitCommitDate,
            'database' => $dbStatus,
            'oracle' => $oracleStatus,
            'extensions' => [
                'oci8' => function_exists('oci_connect'),
                'pdo_oci' => extension_loaded('pdo_oci'),
                'pgsql' => extension_loaded('pgsql'),
                'pdo_pgsql' => extension_loaded('pdo_pgsql'),
                'bcmath' => extension_loaded('bcmath'),
                'gd' => extension_loaded('gd'),
                'zip' => extension_loaded('zip'),
                'intl' => extension_loaded('intl'),
            ],
            'server_time' => now()->toIso8601String(),
            'hostname' => gethostname(),
        ]);
    }

    /**
     * Execute remote deployment / update workflow
     */
    public function deploy(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $action = $request->input('action', 'full'); // full | pull | migrate | cache
        $outputLog = [];
        $startTime = microtime(true);

        Log::info("Remote deployment initiated [Action: {$action}] from IP: " . $request->ip());

        // 1. Git Pull / Sync if git repository exists
        if (in_array($action, ['full', 'pull'], true) && is_dir(base_path('.git'))) {
            $pullCmd = 'git pull origin main 2>&1 || git pull 2>&1';
            $pullOutput = trim((string) @shell_exec($pullCmd));
            $outputLog['git_pull'] = $pullOutput ?: 'Git pull executed';
            Log::info("Git pull output: " . $pullOutput);
        }

        // 2. Database Migrations
        if (in_array($action, ['full', 'migrate'], true)) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $outputLog['migrations'] = trim(Artisan::output()) ?: 'Migrations executed successfully';
            } catch (\Throwable $e) {
                $outputLog['migrations_error'] = $e->getMessage();
                Log::error("Deployment migration error: " . $e->getMessage());
            }
        }

        // 3. Clear and Rebuild Optimizations / Caches
        if (in_array($action, ['full', 'cache'], true)) {
            try {
                Artisan::call('optimize:clear');
                $outputLog['optimize_clear'] = trim(Artisan::output());

                if (app()->environment('production')) {
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    Artisan::call('event:cache');
                    $outputLog['optimize_cache'] = 'Production caches rebuilt successfully';
                }
            } catch (\Throwable $e) {
                $outputLog['cache_error'] = $e->getMessage();
                Log::error("Deployment cache error: " . $e->getMessage());
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'success' => true,
            'message' => "Deployment action '{$action}' completed in {$duration}ms",
            'duration_ms' => $duration,
            'timestamp' => now()->toIso8601String(),
            'output' => $outputLog,
            'status' => [
                'environment' => app()->environment(),
                'git_commit' => trim((string) @shell_exec('git rev-parse --short HEAD 2>&1')) ?: 'unknown',
            ],
        ]);
    }
}
