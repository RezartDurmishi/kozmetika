<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Query\Builder;
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
        $this->middleware('auth:api', ['except' => 'displayImage']);
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
     * @return Builder|JsonResponse|mixed
     */
    public function getProductById($id)
    {
        $product = DB::table('products')->find($id);

        if ($product == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        return $product;
    }

    /**
     * delete by id
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteById($id)
    {
        $product = DB::table('products')->find($id);

        if ($product == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        //delete image before deleting product
        $image = $product->image;
        if ($image != null){
            $imageName = explode("/", $image)[6];
            Storage::disk('public')->delete($imageName);
        }

        DB::table('products')->delete($id);
        return response()->json(['error' => "Product with id " . $id . " deleted successfully."]);
    }

    public function updateById(Request $request, $id)
    {
        $currentProduct = $this->getProductById($id);

        if ($currentProduct == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        $name = $request->name;
        $brand = $request->brand;
        $price = $request->price;
        $description = $request->description;
        $expirationDate = $request->expirationDate;
        $categoryId = $request->categoryId;

        $image = null;
        if ($request->image != null) {
            $image = $this->addImage($request);
        }

        $updatedProduct = ['name' => $name, 'brand' => $brand, 'price' => $price, 'description' => $description,
            'expirationDate' => $expirationDate, 'categoryId' => $categoryId, 'image' => $image];
        DB::table('products')->where('id', $id)->update($updatedProduct);

        return response()->json(['updatedProduct' => $this->getProductById($id)]);
    }

    /**
     * add image
     *
     * @param Request $request
     * @return string|null
     */
    public function addImage(Request $request)
    {
        if ($request->image == null){
            return null;
        }

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
