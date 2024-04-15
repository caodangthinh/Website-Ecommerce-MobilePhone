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

    public function countProduct()
    {
        try {
            $total = Product::count();

            return response()->json([
                'success' => true,
                'total' => $total,
            ], 200);
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Error While Counting Products!',
                'error' => $e->getMessage(), // Return error message for debugging purposes
            ], 400);
        }
    }

    public function listProduct(Request $request)
    {
        try {
            $perPage = 6;
            $page = $request->input('page', 1);

            $products = Product::select('id', 'name', 'price', 'description') // Không chọn trường 'image'
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Error in getting products per page!',
                'error' => $e->getMessage(), // Return error message for debugging purposes
            ], 400);
        }
    }

    public function searchProduct(Request $request)
    {
        try {
            $keyword = $request->input('keyword');

            $results = Product::where('name', 'like', "%$keyword%")
                ->orWhere('description', 'like', "%$keyword%")
                ->select('id', 'name', 'price', 'description')
                ->get();

            return response()->json($results, 200);
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Error in search product API!',
                'error' => $e->getMessage(), // Return error message for debugging purposes
            ], 400);
        }
    }

    public function relatedProduct($pid, $cid)
    {
        try {
            $products = Product::where('category_id', $cid)
                ->where('id', '!=', $pid)
                ->with('category')
                ->limit(3)
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Error in getting related products!',
                'error' => $e->getMessage(), // Return error message for debugging purposes
            ], 400);
        }
    }

    public function productCategory(Request $request, $slug)
    {
        try {
            // Tìm kiếm danh mục dựa trên slug
            $category = Category::where('slug', $slug)->first();

            // Nếu không tìm thấy danh mục, trả về thông báo lỗi
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found!',
                ], 404);
            }

            // Nếu tìm thấy danh mục, tiếp tục tìm kiếm sản phẩm
            $products = Product::where('category_id', $category->id)->with('category')->get(['id', 'name', 'description', 'price']);

            return response()->json([
                'success' => true,
                'category' => $category,
                'products' => $products,
            ], 200);
        } catch (\Exception $error) {
            // Log lỗi nếu cần
            \Log::error($error);

            // Trả về thông báo lỗi phù hợp
            return response()->json([
                'success' => false,
                'message' => 'Error in getting Product!',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

}
