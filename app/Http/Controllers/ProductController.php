<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getAllProduct()
    {
        try {
            $products = Product::with('category')->select('id', 'name', 'description', 'price', 'quantity', 'shipping', 'created_at')
                                ->orderBy('created_at', 'desc')
                                ->limit(12)
                                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Get all Products Successfully!',
                'countTotal' => $products->count(),
                'products' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Getting Products!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
