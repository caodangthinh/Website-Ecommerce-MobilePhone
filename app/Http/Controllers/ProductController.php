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
                'shipping' => 'boolean',
                'category_id' => 'required|exists:categories,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
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

    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'required',
                'description' => 'required',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'shipping' => 'boolean',
                'category_id' => 'required|exists:categories,id',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);

            // Update product fields
            $product->name = $validatedData['name'];
            $product->description = $validatedData['description'];
            $product->price = $validatedData['price'];
            $product->quantity = $validatedData['quantity'];
            $product->shipping = $validatedData['shipping'];
            $product->category_id = $validatedData['category_id'];

            // Handle image update
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $image->getClientOriginalName();
                if ($product->image) {
                    unlink(public_path('images/' . $product->image));
                }
                $image->move(public_path('images'), $imageName);
                $product->image = $imageName;
            }


            // Save the updated product
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in updating product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Delete Product Successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Deleting Product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSingleProduct($slug)
    {
        try {
            $product = Product::where('slug', $slug)->with('category:id,name')->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Get single Product Successfully!',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Getting single Product!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function productFilters(Request $request)
    {
        try {
            $checked = $request->input('checked', []);
            $radio = $request->input('radio', []);

            $query = Product::query();

            if (count($checked) > 0) {
                $query->whereIn('category', $checked);
            }

            if (count($radio) === 2) {
                $query->where('price', '>=', $radio[0])
                    ->where('price', '<=', $radio[1]);
            }

            $products = $query->get();

            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error While Filtering Products!',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

}
