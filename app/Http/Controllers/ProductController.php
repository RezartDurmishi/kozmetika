<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Create a new ProductController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Create product
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $name = $request->name;
        $brand = $request->brand;
        $price = $request->price;
        $description = $request->description;
        $expirationDate = $request->expirationDate;
        $categoryId = $request->categoryId;

        //$image = $request->image;

        $product = Product::create(compact('name', 'brand', 'price', 'description', 'expirationDate', 'categoryId'));

        return response()->json(['data' => $product], 201);
    }

    public function list()
    {
        $response = DB::table('products')->select('products.*')->get();
    }

}
