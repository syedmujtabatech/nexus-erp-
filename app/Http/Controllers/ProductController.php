<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $products = Product::where('company_id', $companyId)->latest()->get();
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
        ]);

        $product = Product::create([
            'company_id' => Auth::user()->current_company_id,
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'description' => $request->description,
        ]);

        ActivityLogger::log('create_product', "Added {$product->type}: {$product->name} (Price: {$product->sale_price})");

        return redirect()->back()->with('success', 'Product/Service added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'description' => $request->description,
        ]);

        ActivityLogger::log('update_product', "Updated {$product->type}: {$product->name}");

        return redirect()->back()->with('success', 'Product/Service updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        ActivityLogger::log('delete_product', "Deleted product/service: {$name}");

        return redirect()->route('products.index')->with('success', "Product/Service '{$name}' deleted successfully.");
    }
}
