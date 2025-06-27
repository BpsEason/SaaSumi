<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Modules\LineNotify\LineNotifyService;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware(['web', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])->group(function () {

    Route::get('/line-notify/redirect', function (Request $request) {
        // Handle LINE Notify redirect and save token for the tenant
        if ($request->get('code')) {
            $service = app(LineNotifyService::class);
            $accessToken = $service->getAccessToken($request->get('code'));
            if ($accessToken) {
                // Save the token to the current tenant's settings
                $tenant = tenant();
                $tenant->line_notify_token = $accessToken;
                $tenant->save();
                return 'LINE Notify 綁定成功！';
            }
        }
        return 'LINE Notify 綁定失敗。';
    })->name('line-notify.redirect');
});

Route::middleware(['api', 'auth:sanctum'])->prefix('api')->group(function () {
    Route::post('/line-notify/send-message', function (Request $request) {
        $service = app(LineNotifyService::class);
        $message = $request->input('message');
        $tenant = tenant();

        if (!$tenant || !$tenant->line_notify_token) {
            return response()->json(['error' => 'LINE Notify token not found for this tenant.'], 404);
        }

        $success = $service->sendMessage($tenant->line_notify_token, $message);
        if ($success) {
            return response()->json(['status' => 'Message sent successfully.']);
        }

        return response()->json(['status' => 'Failed to send message.'], 500);
    });

    Route::post('/line-notify/revoke', function () {
        $tenant = tenant();
        if (!$tenant || !$tenant->line_notify_token) {
            return response()->json(['error' => 'LINE Notify token not found for this tenant.'], 404);
        }

        $service = app(LineNotifyService::class);
        $success = $service->revokeToken($tenant->line_notify_token);

        if ($success) {
            $tenant->line_notify_token = null;
            $tenant->save();
            return response()->json(['status' => 'Token revoked successfully.']);
        }
        return response()->json(['status' => 'Failed to revoke token.'], 500);
    });
});
