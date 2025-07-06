<?php

namespace App\GraphQL\Mutations\Products;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserStore;
use App\Models\Product;
use App\Models\ProductsAttributesValues;
use Exception;
use Illuminate\Validation\ValidationException;


class ProductResolver
{
    public function create($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to create a store.'],
                ]);
            }
            
            $store = Store::findOrFail($args['store_id']);
            $userStore = UserStore::where('user_id', $user->id)
                        ->where('store_id', $store->id)
                        ->firstOrFail();
            if ($userStore->role !== 'owner') {
                throw ValidationException::withMessages([
                    'message' => ['Only owner can create product.'],
                ]);
            }
            
            $product = Product::create([
                'store_id' => $store->id,
                'name' => $args['name'],
                'description' => $args['description'],
                'price' => $args['price'],
            ]);

            if (!empty($args['attributes'])) {
                foreach ($args['attributes'] as $attr) {
                    $productAttributeValue = ProductsAttributesValues::create([
                        'product_id' => $product->id,
                        'attribute_id' => $attr['attribute_id'],
                        'value' => $attr['value'],
                    ]);
                }
            }

            return $product;
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }

    public function update($_, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw ValidationException::withMessages([
                    'message' => ['You must be logged in to update a product.'],
                ]);
            }

            $product = Product::findOrFail($args['id']);
            $store = Store::findOrFail($product->store_id);

            $userStore = UserStore::where('user_id', $user->id)
                ->where('store_id', $store->id)
                ->firstOrFail();

            if ($userStore->role !== 'owner') {
                throw ValidationException::withMessages([
                    'message' => ['Only owner can update product.'],
                ]);
            }

            $product->update([
                'name' => $args['name'] ?? $product->name,
                'description' => $args['description'] ?? $product->description,
                'price' => $args['price'] ?? $product->price,
            ]);

            if (!empty($args['attributes'])) {
                foreach ($args['attributes'] as $attr) {
                    $existing = ProductsAttributesValues::where('product_id', $product->id)
                        ->where('attribute_id', $attr['attribute_id'])
                        ->first();

                    if ($existing) {
                        if ($existing->value !== $attr['value']) {
                            $existing->update(['value' => $attr['value']]);
                        }
                    } else {
                        ProductsAttributesValues::create([
                            'product_id' => $product->id,
                            'attribute_id' => $attr['attribute_id'],
                            'value' => $attr['value'],
                        ]);
                    }
                }
            }

            return $product;

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

            $product = Product::findOrFail($args['id']);
            $store = Store::findOrFail($product->store_id);

            $userStore = UserStore::where('user_id', $authUser->id)
                ->where('store_id', $store->id)
                ->firstOrFail();

            if ($userStore->role !== 'owner') {
                throw ValidationException::withMessages([
                    'message' => ['Only owner can delete product attribute.'],
                ]);
            }

            # Delete all associated values first
            ProductsAttributesValues::where('product_id', $product->id)->delete();

            # delete the product itself
            $product->delete();

            return true;

        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
        }
    }
}
