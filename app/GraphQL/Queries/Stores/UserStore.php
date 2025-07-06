<?php

namespace App\GraphQL\Queries\Stores;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Product;
use Exception;
use Illuminate\Validation\ValidationException;


class UserStore
{
    public function getUsersByStore($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to create a store.'],
                ]);
            }

            $store = Store::find($args['store_id']);
            if (!$store) {
                throw ValidationException::withMessages([
                    'error' => ['Store not exists.'],
                ]);
            }

            $users = User::select('ustr.id', 'ustr.role AS store_role', 'usr.full_name', 'usr.email', 'ustr.created_at', 'ustr.updated_at')
                ->from('user_stores AS ustr')
                ->leftJoin('users AS usr', 'usr.id', '=', 'ustr.user_id')
                ->where('ustr.store_id', $args['store_id'])
                ->get();


            return $users;
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }
}
