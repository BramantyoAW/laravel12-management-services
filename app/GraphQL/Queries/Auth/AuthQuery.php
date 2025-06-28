<?php

namespace App\GraphQL\Queries\Auth;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;


class AuthQuery
{
    public function me($_, array $args)
    {
        $user = Auth::user();
    
        $exp = auth()->payload()->get('exp');
        $now = now()->timestamp;

        return [
            'user' => $user,
            'expires_in' => $exp - $now,
            'expired_status' => $exp < $now,
        ];
    }
}
