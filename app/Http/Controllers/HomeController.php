<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Product;

class HomeController extends Controller
{
   public function index(): Response
    {
        $featuredProducts = Product::whereIn('id', [1, 2, 3, 4])->get();

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
