<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use AuthorizesRequests;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|between:1,5',
            'comment'    => 'required|string|max:500',
        ]);

        $user = auth()->user();
        if (!$user) {
            return back()->withErrors([
                'message' => 'Vous devez être connecté pour laisser un avis.'
            ])->withInput();
        }

        // Vérifier si doublon d'avis
        if (Review::where('user_id', $user->id)->where('product_id', $request->product_id)->exists()) {
            return back()->withErrors(['message' => 'Vous avez déjà laissé un avis pour ce produit. Supprimez-le avant d\'en ajouter un autre.']);
        }

        Review::create([
            'user_id'    => $user->id,
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return back()->with('success', 'Avis ajouté avec succès.');
    }




    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Review $review)
{
      $this->authorize('update', $review);
}
    /**
     * Remove the specified resource from storage.
     */
public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back()->with('success', 'Votre avis a été supprimé.');
    }
}

