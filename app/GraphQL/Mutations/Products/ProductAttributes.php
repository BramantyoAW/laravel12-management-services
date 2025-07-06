<?php

namespace App\GraphQL\Mutations\Products;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserStore;
use App\Models\ProductsAttributes;
use App\Models\ProductsAttributesValues;
use Exception;
use Illuminate\Validation\ValidationException;


class ProductAttributes
{
    public function create($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to create a product attribute.'],
                ]);
            }
    
            $store = Store::findOrFail($args['store_id']);
            $userStore = UserStore::where('user_id', $authUser->id)
                ->where('store_id', $store->id)
                ->firstOrFail();
    
            if ($userStore->role !== 'owner') {
                throw ValidationException::withMessages([
                    'message' => ['Only store owner can create product attribute.'],
                ]);
            }
    
            $existing = ProductsAttributes::where('store_id', $store->id)
                ->where('name', $args['name'])
                ->first();
    
            if ($existing) {
                throw ValidationException::withMessages([
                    'message' => ['Attribute with this name already exists in this store.'],
                ]);
            }
    
            $productAttribute = ProductsAttributes::create([
                'store_id' => $store->id,
                'name' => $args['name'],
                'type' => $args['type'],
            ]);
    
            return $productAttribute;
    
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }

    public function delete($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to delete product attribute.'],
                ]);
            }

            $attribute = ProductsAttributes::findOrFail($args['id']);
            $store = Store::findOrFail($attribute->store_id);

            $userStore = UserStore::where('user_id', $authUser->id)
                ->where('store_id', $store->id)
                ->firstOrFail();

            if ($userStore->role !== 'owner') {
                throw ValidationException::withMessages([
                    'message' => ['Only store owner can delete product attribute.'],
                ]);
            }

            #delete all attribute values
            ProductsAttributesValues::where('attribute_id', $attribute->id)->delete();

            $attribute->delete();

            return true;

        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }

    
}
