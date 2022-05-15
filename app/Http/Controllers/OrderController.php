<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * @var AuthController
     */
    private $authController;

    /**
     * constructor
     */
    public function __construct(AuthController $authController)
    {
        $this->middleware('auth:api');
        $this->authController = $authController;
    }

    /**
     * list all orders
     *
     * @return JsonResponse
     */
    public function list()
    {
        $response = null;
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($loggedUser->role == 'admin') {
            $response = DB::table('orders')->where(
                [['product_id', '=', null], ['status', '!=', 'CANCELED']])->get();
        }

        if ($loggedUser->role == 'user') {
            $userId = $loggedUser->id;
            $response = DB::table('orders')
                ->where([['product_id', '=', null], ['user_id', '=', $userId]])->get();
        }

        return response()->json(['data' => $response]);
    }

    /**
     * get by id
     *
     * @param $id
     * @return Builder|JsonResponse|mixed
     */
    public function getOrderById($id)
    {
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        $order = null;
        if ($loggedUser->role == 'user') {
            $order = DB::table('orders')
                ->where([['id', '=', $id], ['user_id', '=', $loggedUser->id]])->get();
        }

        if ($loggedUser->role == 'admin') {
            $order = DB::table('orders')->where(
                ['id', '=', $id], ['status', '!=', 'CANCELED'])->get();
        }

        if ($order == null || $order->isEmpty() || $order[0]->status == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        $suborders = DB::table('orders')->where('parent_id', $order[0]->id)->get();
        return response()->json(["order" => $order, "suborders" => $suborders]);
    }


    /**
     * create order
     */
    public function create(Request $request)
    {
        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($loggedUser->role == 'admin') {
            return response()->json(['error' => 'Register as an user to create orders.'], 401);
        }

        $order = new Order();
        $order->user_id = $loggedUser->id;
        $order->address = $request->address;
        $order->orderDate = $request->orderDate;
        $order->total = $request->total;
        $order->quantity = 0;
        $order->price = 0;
        $order->status = "WAITING FOR APPROVAL";
        $order->parent_id = null;
        $order->save();

        $parentId = $order->id;

        $items = $request->items;
        foreach ($items as $item) {
            $orderItem = new Order();
            $orderItem->product_id = $item['productId'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->total = $item['subtotal'];
            $orderItem->price = $item['price'];
            $orderItem->parent_id = $parentId;
            $orderItem->orderDate = $request->orderDate;
            $orderItem->address = $request->address;
            $orderItem->status = null;
            $orderItem->user_id = $loggedUser->id;
            $orderItem->save();
        }

        $suborders = DB::table('orders')->where('parent_id', $parentId)->get();
        return response()->json(["order" => $order, "suborders" => $suborders], 201);
    }

    /**
     * cancel order
     *
     * @param $id
     * @return mixed
     *
     */
    public function cancelOrder($id)
    {
        $order = Order::find($id);
        if ($order == null || $order->status == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        $loggedUser = json_decode($this->authController->getLoggedUser()->content());
        if ($loggedUser->role == 'admin' && $order->status != 'CANCELED') {
            $order->status = 'REJECTED';
            $order->save();
            return response()->json(['canceledOrder' => $order]);
        }

        if ($loggedUser->role == 'user' && $loggedUser->id == $order->user_id) {
            if ($order->status == 'WAITING FOR APPROVAL') {
                $order->status = 'CANCELED';
                $order->save();
                return response()->json(['canceledOrder' => $order]);
            } else {
                return response()->json(['error' => "Order with id " . $id . " cannot be canceled."], 404);
            }
        } else {
            return response()->json(['error' => "Order with id " . $id . " is not found."], 404);
        }
    }

    /**
     * approve order
     *
     * @return mixed
     */
    public function approveOrder($id)
    {
        $order = Order::find($id);
        if ($order == null || $order->status == 'CANCELED' || $order->status == null) {
            return response()->json(['error' => "Product with id " . $id . " is not found."], 404);
        }

        $order->status = 'APPROVED';
        $order->save();
        return response()->json(['approvedOrder' => $order]);
    }
}
