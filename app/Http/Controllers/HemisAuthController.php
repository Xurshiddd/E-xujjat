<?php

namespace App\Http\Controllers;

use App\Services\HemisOAuthClientService;
use Illuminate\Http\Request;
use Log;

class HemisAuthController extends Controller
{
    public function __construct(private HemisOAuthClientService $service)
    {
    }
    public function redirectToHemis()
    {
        $authorizationUrl = $this->service->provider()->getAuthorizationUrl();
        $state = $this->service->provider()->getState();
        cookie()->queue('oauth2state', $state, 10);
        return redirect()->away($authorizationUrl);
    }
    public function login(Request $request)
    {
        $cookieState = $request->cookie('oauth2state');
        Log::info('Hemis login attempt', [
            'state' => $request->state,
            'code' => $request->code,
        ]);
        Log::info('cookie state', [
            'oauth2state' => $cookieState,
        ]);
        
        if ($request->state !== $cookieState) {
            return abort(403, 'Invalid state');
        }
        try {
            $accessToken = $this->service->provider()->getAccessToken('authorization_code', [
                'code' => $request->code
            ]);
            
            $resourceOwner = $this->service->provider()->getResourceOwner($accessToken);
            $userData = $resourceOwner->toArray();
            dd($userData);
        }catch (\Exception $e) {
            return abort(500, 'Error during authentication: ' . $e->getMessage());
        }
    }
}
