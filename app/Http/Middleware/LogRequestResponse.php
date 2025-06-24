<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $data = $request->all();
        // If logging an authentication request, mask the password in the log
        if ($request->isMethod('post') && $request->path() === 'api/auth/login' && isset($data['password'])) {
            $data['password'] = 'REDACTED';  // Mask the password
        }

        // Continue processing the request
        $response = $next($request);

        $driver = config('logging.api_log_driver', env('API_LOG_DRIVER', 'file'));

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user' => optional(auth('sanctum')->user())->id,
            'request_headers' => json_encode($request->headers->all()),
            'request_body' => json_encode($data),
            'response_status' => $response->getStatusCode(),
            'response_headers' => json_encode($response->headers->all()),
            'response_body' => $response->getContent()
        ];

        // insert logs
        if ($driver === 'db') {
            ApiLog::create($logData);
        } else {
            Log::info('API Request', $logData);
        }
        return $response;
    }
}
