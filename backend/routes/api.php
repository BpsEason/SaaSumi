<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Services\AIProxyService;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\DashboardController;
use App\Models\User; // Assuming User model for authentication

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes for authentication
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'tenant_domain' => 'required|string', // Added for tenant context
    ]);

    // Simplified authentication for demo:
    // In a real multi-tenant app, you'd load a user from the tenant-specific database
    // For now, a mock user system based on the email.
    // Ensure you have a 'users' table in your central DB or tenant DB if you go full multi-DB.
    // For this demo, let's allow 'admin@example.com' with 'password' for any tenant.
    $user = User::where('email', $credentials['email'])->first();

    if ($user && Hash::check($credentials['password'], $user->password)) {
        // Create a Sanctum token
        $token = $user->createToken('auth_token', ['server:update'])->plainTextToken;
        return response()->json(['message' => 'Login successful', 'token' => $token]);
    }

    return response()->json(['message' => 'Invalid credentials or tenant not found'], 401);
});

// Mock registration (for demo purposes) - In a real app, this would be more complex
Route::post('/register', function (Request $request) {
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'tenant_domain' => 'required|string',
    ]);

    // For a multi-tenant setup, this would create a user in the specific tenant's DB
    // For this central API, we'll create a dummy user in the central `users` table.
    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
    ]);

    $token = $user->createToken('auth_token', ['server:update'])->plainTextToken;
    return response()->json(['message' => 'Registration successful', 'token' => $token], 201);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // AI Recommendation API Proxy
    Route::post('/ai/recommend', function (Request $request) {
        $keywords = $request->input('keywords');
        $service = app(AIProxyService::class);
        $recommendations = $service->recommendRooms($keywords);

        return response()->json($recommendations);
    });

    // LINE Notify Authentication URL generation
    Route::get('/line-notify/auth-url', function (Request $request) {
        $service = app(\App\Modules\LineNotify\LineNotifyService::class);
        // Using current tenant ID for state, ensure this is handled securely
        $state = 'tenant-' . (tenant() ? tenant()->id : 'default');
        $authUrl = $service->getAuthUrl($state);
        return response()->json(['auth_url' => $authUrl]);
    });

    // Guest Management API (New)
    Route::apiResource('/guests', GuestController::class);

    // Dashboard KPI API (New)
    Route::get('/dashboard/kpis', [DashboardController::class, 'getKpis']);
});
