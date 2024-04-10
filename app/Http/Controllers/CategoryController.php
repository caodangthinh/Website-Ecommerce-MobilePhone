<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function createCategory(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', 
            ]);
    
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
    
            $image->move(public_path('images'), $imageName);
    
            $category = new Category();
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            $category->image = $imageName;
            $category->save();
    
            return response()->json([
                'success' => true,
                'message' => 'New Category created!',
                'category' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Creating Category!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $request->validate([
                'name' => 'required',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', 
            ]);

            $category->name = $request->name;
            $category->slug = Str::slug($request->name);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = $image->getClientOriginalName();

                if ($category->image) {
                    unlink(public_path('images/' . $category->image));
                }

                $image->move(public_path('images'), $imageName);
                $category->image = $imageName;
            }

            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in updating category!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCategory(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Delete Category Successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in Deleting Category!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
