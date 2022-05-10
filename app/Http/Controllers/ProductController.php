<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Create a new ProductController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => 'displayImage', 'list', 'getById']);
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

        $image = $this->addImage($request);

        $product = Product::create(compact('name', 'brand', 'price', 'description', 'expirationDate', 'categoryId', 'image'));

        return response()->json(['data' => $product], 201);
    }

    /**
     * list all products
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $response = DB::table('products')->select('products.*')->get();
        return response()->json(['data' => $response]);
    }

    /**
     * get by id
     *
     * @param $id
     * @return void
     */
    public function getById($id){
        $product = DB::table('products')->find($id);
        return $product;
    }

    /**
     * add image
     *
     * @param Request $request
     * @return string
     */
    public function addImage(Request $request): string
    {
        $base64 = $request->image;
        $base64 = explode(",", $base64)[1];
        $imageName = Str::random(10) . '.png';

        //storage/app/public
        Storage::disk('public')->put($imageName, base64_decode($base64));

        return 'http://localhost:8000/api/product/image/' . $imageName;
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function displayImage(Request $request)
    {
        $fileName = $request->fileName;
        $path = storage_path() . '\app\public\\' . $fileName;

        if (Storage::disk('public')->exists($fileName) == true) {
            $img = file_get_contents($path);
            return response($img)->header('Content-type', 'image/png');
        }

        return response()->json(['url' => 'Image not found.'], 404);
    }
}
