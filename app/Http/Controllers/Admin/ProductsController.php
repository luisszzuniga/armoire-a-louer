<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('updated_at', 'desc')->with(['brand', 'category', 'materials'])->get();
        $menCategories = Category::where('sex', Category::HOMME)->get();
        $womenCategories = Category::where('sex', Category::FEMME)->get();
        $brands = Brand::get();
        $materials = Material::get();
        
        return Inertia::render('Admin/Products', [
            'products' => $products,
            'menCategories' => $menCategories,
            'womenCategories' => $womenCategories,
            'brands' => $brands,
            'seasons' => Product::SEASONS,
            'materials' => $materials
        ]);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->only(['name', 'price_per_day', 'category_id', 'brand_id', 'description', 'active', 'season']));
        $product->addMediaFromRequest('image')->toMediaCollection('products');
        $product->materials()->sync($request->input('materials_ids'));

        $products = Product::orderBy('updated_at', 'desc')->with(['brand', 'category', 'materials'])->get();
        return response()->json($products);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->only(['name', 'price_per_day', 'category_id', 'brand_id', 'description', 'active', 'season']));
        if ($request->hasFile('image')) {
            $product->media()->delete();
            $product->addMediaFromRequest('image')->toMediaCollection('products');
        }
        $product->materials()->sync($request->input('materials_ids'));
        $product->save();

        $products = Product::orderBy('updated_at', 'desc')->with(['brand', 'category', 'materials'])->get();
        return response()->json($products);
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        $products = Product::orderBy('updated_at', 'desc')->with(['brand', 'category', 'materials'])->get();
        return response()->json($products);
    }
}
