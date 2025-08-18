<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $filters = request()->only(['search', 'category_id']);

    $products = \App\Models\Product::with('category')
        ->when($filters['search'] ?? null, fn ($q, $s) =>
            $q->where(fn ($qq) => $qq->where('name', 'like', "%$s%")
                                     ->orWhere('slug', 'like', "%$s%"))
        )
        ->when($filters['category_id'] ?? null, fn ($q, $cid) => $q->where('category_id', $cid))
        ->latest()
        ->paginate(12)
        ->through(fn ($p) => tap($p)->append('image_url'))   // ← important
        ->withQueryString();

    return \Inertia\Inertia::render('Admin/Products/Index', [
        'products'   => $products,
        'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        'filters'    => $filters,
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        Product::create($request->validated());
        return redirect()->route('admin.products.index')->with('success', 'Produit créé.');
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'old_price' => $product->old_price,
            'stock' => $product->stock, //
            'image_url' => asset('images/produits/' . $product->image),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'reviews' => $product->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ],
                ];
            }),
            'is_favorite' => auth()->check()
                ? auth()->user()->favorites->contains($product->id)
                : false,
            'reviews_count' => $product->reviews()->count(),
            'average_rating' => round($product->reviews()->avg('rating') ?? 0, 1),
        ];

        // Produits similaires de la même catégorie
        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($similar) {
                return [
                    'id' => $similar->id,
                    'name' => $similar->name,
                    'slug' => $similar->slug,
                    'price' => $similar->price,
                    'old_price' => $similar->old_price,
                    'image_url' => asset('images/produits/' . $similar->image),
                ];
            });

        return Inertia::render('Products/Show', [
            'product' => $productData,
            'similarProducts' => $similarProducts,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produit supprimé.');
    }
}
