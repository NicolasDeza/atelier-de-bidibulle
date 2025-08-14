<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class ProductController extends Controller
{
        public function index()
{
    $filters = request()->only(['search','category_id']);

    $products = \App\Models\Product::with('category')
        ->when($filters['search'] ?? null, fn($q,$s) =>
            $q->where(fn($qq)=> $qq->where('name','like',"%$s%")->orWhere('slug','like',"%$s%"))
        )
        ->when($filters['category_id'] ?? null, fn($q,$cid)=> $q->where('category_id',$cid))
        ->latest()
        ->paginate(12)
        ->withQueryString();

    return \Inertia\Inertia::render('Admin/Products/Index', [
        'products' => $products,
        'categories' => \App\Models\Category::orderBy('name')->get(['id','name']),
        'filters' => $filters,
    ]);
}

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(['id','name'])
        ]);
    }

    public function store(ProductRequest $request)
    {
        Product::create($request->validated());
        return redirect()->route('admin.products.index')->with('success', 'Produit créé.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id','name'])
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produit supprimé.');
    }
}
