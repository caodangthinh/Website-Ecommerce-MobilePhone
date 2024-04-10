<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
                'shipping' => 'required|boolean',
                'category_id' => 'required|exists:categories,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
            ]);
    
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
    
            // Lưu tệp tin vào thư mục public/images
            $image->move(public_path('images'), $imageName);
    
            $product = new Product();
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->description = $request->description;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->category_id = $request->category_id;
            $product->image = $imageName; // Lưu tên file ảnh vào cơ sở dữ liệu
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

    public function getProductImage($id)
    {
        try {
            $product = Product::findOrFail($id);
            if (!$product->image) {
                abort(404);
            }
            $imagePath = public_path('images/' . $product->image);
    
            if (!file_exists($imagePath)) {
                abort(404);
            }
            return response()->file($imagePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Getting Image of Product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProduct(Request $request, $pid)
    {
        try {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'category' => 'required',
                'shipping' => 'required',
                'image' => 'image|max:1000', // max 1MB
            ]);

            $product = Product::findOrFail($pid);

            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->description = $request->description;
            $product->price = $request->price;
            $product->quantity = $request->quantity;
            $product->category = $request->category;
            $product->shipping = $request->shipping;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images');
                $product->image = $imagePath;
            }

            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Update Product Successfully!',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Updating Product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
