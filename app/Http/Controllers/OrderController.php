<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;

class OrderController extends Controller
{
    // عرض الطلبات في الصفحة
    // استرجاع كل الطلبات للمستخدم الحالي
    public function index(Request $request)
    {
         // افتراضاً، $request->user() تعيد المستخدم المصادق عليه عبر Sanctum
         $orders = $request->user()->orders;
         return response()->json([
              'orders' => $orders
         ]);
    }

    // إنشاء طلب جديد
    public function store(Request $request)
    {
         $validated = $request->validate([
             'due_date'       => 'required|date',
             'client_name'    => 'required|string|max:255',
             'client_contact' => 'required|string|max:255',
             'amount'         => 'required|numeric',
             'status'         => 'required|string|max:100'
         ]);

         // تعيين معرف المستخدم الحالي
         $validated['user_id'] = $request->user()->id;

         $order = Order::create($validated);

         return response()->json([
              'message' => 'Order created successfully',
              'order'   => $order
         ], 201);
    }

    // تعديل حالة الطلب
    public function update(Request $request, $id)
    {
         $order = Order::findOrFail($id);

         // التأكد من أن الطلب يخص المستخدم الحالي
         if ($order->user_id !== $request->user()->id) {
              return response()->json(['message' => 'Unauthorized'], 403);
         }

         $validated = $request->validate([
             'status' => 'required|string|max:100'
         ]);

         $order->update($validated);

         return response()->json([
              'message' => 'Order updated successfully',
              'order'   => $order
         ]);
    }
    
    public function getUserOrders($userId)
    {
        // جلب الطلبات التي تخص user_id المحدّد
        $orders = Order::where('user_id', $userId)->get();

        // إعادة الاستجابة بشكل JSON
        return response()->json([
            'orders' => $orders
        ]);
    }


}
