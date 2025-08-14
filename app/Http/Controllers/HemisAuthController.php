<?php

namespace App\Http\Controllers;

use App\Services\HemisOAuthClientService;
use Illuminate\Http\Request;

class HemisAuthController extends Controller
{
    public function __construct(private HemisOAuthClientService $service)
    {
    }
    public function redirectToHemis()
    {
        $authorizationUrl = $this->service->provider()->getAuthorizationUrl();
        session(['oauth2state' => $this->service->provider()->getState()]);
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
            dd($userData);
        }catch (\Exception $e) {
            return abort(500, 'Error during authentication: ' . $e->getMessage());
        }
    }
}
