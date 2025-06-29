<?php

namespace App\GraphQL\Mutations\Logout;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

use Exception;

class Logout
{
    /**
     * Handle the GraphQL login mutation.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function logout($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            JWTAuth::setToken($args['token'])->invalidate();
            return [
                'status' => true,
                'message' => 'Logged out successfully',
            ];
        } catch (Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to logout',
            ];
        }
    }
}
