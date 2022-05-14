<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * list all categories
     *
     * @return JsonResponse
     */
    public function list()
    {
        $response = DB::table('categories')->select('categories.*')->get();
        return response()->json(['data' => $response]);
    }

    /**
     * get by id
     *
     * @param $id
     * @return Builder|JsonResponse|mixed
     */
    public function getCategoryById($id)
    {
        $category = DB::table('categories')->find($id);
        if ($category == null) {
            return response()->json(['error' => "Category with id " . $id . " is not found."], 404);
        }

        return $category;
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
        $category = Category::create(compact('name'));
        return response()->json(['data' => $category], 201);
    }

    /**
     * delete by id
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteById($id)
    {
        $category = DB::table('categories')->find($id);

        if ($category == null) {
            return response()->json(['error' => "Category with id " . $id . " is not found."], 404);
        }
        DB::table('categories')->delete($id);
        return response()->json(['message' => "Category with id " . $id . " deleted successfully."]);
    }

    /**
     * Update category by id
     *
     * @return void|mixed
     */
    public function updateById(Request $request, $id)
    {
        $currentCategory = Category::find($id);
        if ($currentCategory == null) {
            return response()->json(['error' => "Category with id " . $id . " is not found."], 404);
        }

        $currentCategory->name = $request->name;
        $currentCategory->save();
        return response()->json(['updatedCategory' => $this->getCategoryById($id)]);
    }
}
