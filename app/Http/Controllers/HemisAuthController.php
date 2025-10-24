<?php
namespace App\Http\Controllers;

use App\Services\HemisOAuthClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Log;

class HemisAuthController extends Controller
{
    public function __construct(private HemisOAuthClientService $service)
    {
    }
    public function redirectToHemis()
    {
        $authorizationUrl = $this->service->provider()->getAuthorizationUrl();
        session(['oauth2state' => $this->service->provider()->getState()]);
        Log::info('Redirecting to Hemis', [
            'authorization_url' => $authorizationUrl,
            'oauth2state' => session('oauth2state'),
        ]);
        return redirect()->away($authorizationUrl);
    }
    public function login(Request $request)
    {
        if ($request->state !== session('oauth2state')) {
            return abort(403, 'Invalid state');
        }
        try {
            $accessToken = $this->service->provider()->getAccessToken('authorization_code', [
                'code' => $request->code
            ]);

            $resourceOwner = $this->service->provider()->getResourceOwner($accessToken);
            $userData = $resourceOwner->toArray();
            if (!$userData) {
                return abort(500, 'Failed to retrieve user data from Hemis');
            }
            $user = User::where('hemis_id_number', $userData['employee_id_number'])->first();
            if ($user){
                Auth::login($user);
                Log::info('User created and logged in', [
                'user_id' => $user->id,
                'hemis_id_number' => $user->hemis_id_number,
                'email' => $user->email,
            ]);
                return redirect()->route('dashboard');
            }
            $user = User::create([
                'name' => $userData['name'],
                'email' => strtolower($userData['firsname']). time() .'@gmail.com',
                'password' => Hash::make($userData['passport_number']), // Temporary password
                'hemis_id_number' => $userData['employee_id_number'],
                'is_admin' => false, // Default value, adjust as needed
                'avatar' => $userData['picture'] ?? null,
                'phone' => $userData['phone'] ?? null,
                'department' => $userData['departments']['department']['name'] ?? null,
                'staffPosition' => $userData['departments']['staffPosition']['name'] ?? null,
            ]);
            Log::info('User created and logged in', [
                'user_data' => $userData,
            ]);
            Auth::login($user);
            return redirect()->route('dashboard');
        }catch (\Exception $e) {
            Log::error('Failed to login with Hemis', [
                'error_message' => $e->getMessage(), $userData
            ]);
            return redirect()->route('home')->withErrors(['error' => 'Failed to login with Hemis: ' . $e->getMessage()]);
        }
    }
}
