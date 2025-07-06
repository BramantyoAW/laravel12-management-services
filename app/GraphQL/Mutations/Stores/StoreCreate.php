<?php

namespace App\GraphQL\Mutations\Stores;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;
use Exception;
use Illuminate\Validation\ValidationException;

class StoreCreate
{
    public function main($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = Auth::user();
        if (!$user) {
            throw ValidationException::withMessages([
                'message' => ['You must be logged in to create a store.'],
            ]);
        }

        if ($user->role !== 'admin') {
            throw ValidationException::withMessages([
                'message' => ['You must be an admin to create a store.'],
            ]);
        }
        
        $storeExist = $this->storeExist($args['name']);
        if ($storeExist) {
            throw ValidationException::withMessages([
                'message' => ['Store already exists.'],
            ]);
        }

        $store = Store::create([
            'name' => $args['name'],
        ]);

        return $store;
    }

    private  function storeExist($name)
    {
        $storeExist = Store::where('name', 'LIKE', "%{$name}%")->first();

        return $storeExist;
    }

}
