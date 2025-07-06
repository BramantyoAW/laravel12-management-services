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
use Illuminate\Support\Facades\Log;


class Stores
{
    public function getAllStore($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {

            $user = Auth::user();
            if (!$user) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to create a store.'],
                ]);
            }
            $stores = Store::paginate($args['limit'] ?? 10);
            
            $result = [
                'data' => $stores->items(),
                'pagination' => [
                    'total' => $stores->total(),
                    'count' => $stores->count(),
                    'per_page' => $stores->perPage(),
                    'current_page' => $stores->currentPage(),
                    'total_pages' => $stores->lastPage(),
                ],
            ];

            Log::info(print_r($result, true));
            return $result;
        } catch (Exception $e) {
            echo  $e->getMessage();
            return ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }

    public function getStoreById($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to create a store.'],
                ]);
            }
            return Store::findOrFail($args['id']);
        } catch (ModelNotFoundException $e) {
            throw ValidationException::withMessages([
                'error' => ['Store not exists.'],
            ]);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }
}
