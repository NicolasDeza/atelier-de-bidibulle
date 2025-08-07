<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->where('stock', '>', 0);

        // Filtrer par catégorie si le paramètre category est présent
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $products = $query->paginate(12);

        // Récupérer toutes les catégories pour les filtres avec le nombre de produits
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }])->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $request->category ?? 'all'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    // ✅ Produits similaires (même catégorie, excluant le produit actuel)
    $similarProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->latest()
        ->take(4)
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}

