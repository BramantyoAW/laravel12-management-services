<?php

namespace App\GraphQL\Mutations\Stores;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserStore;
use Exception;
use Illuminate\Validation\ValidationException;


class AssignUserToStore
{
    public function main($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $store = Store::findOrFail($args['store_id']);
            $user = User::findOrFail($args['user_id']);

            if (!in_array($args['role'] ?? 'staff', ['staff', 'owner'])) {
                throw ValidationException::withMessages([
                    'message' => ['Role must be staff or owner.'],
                ]);
            }
            $store->users()->syncWithoutDetaching([
                $user->id => ['role' => $args['role'] ?? 'staff']
            ]);
            
            $assign = UserStore::where('user_id', $user->id)
                            ->where('store_id', $store->id)
                            ->firstOrFail();
            return [
                'message' => "Assign user {$user->name} to store {$store->name} success."
            ];

        } catch (Exception $e) {
            echo  $e->getMessage();
            throw ValidationException::withMessages([
                'message' => ['User or store not exists.'],
            ]);
        }
    }
}
