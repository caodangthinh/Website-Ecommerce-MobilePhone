<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function getAllProduct()
    {
        try {
            $products = Product::with('category:id,name')->select('id', 'name', 'slug', 'category_id', 'description', 'price', 'quantity', 'shipping', 'created_at')
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

    public function createProduct(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'category_id' => 'required|exists:categories,id',
                'image' => 'required|image|max:1000', // max 1MB
            ]);

            $imagePath = $request->file('image')->store('images');

            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->description = $request->description;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->category_id = $request->category_id;
            $product->image = $imagePath;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'New Product created!',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Creating Product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
