<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // محاولة تسجيل الدخول كـ Member
        $member = Member::where('email', $request->email)->first();
        if ($member && Hash::check($request->password, $member->password)) {
            $member->tokens()->delete(); // حذف التوكنات القديمة
            $token = $member->createToken('member_token')->plainTextToken;
            return response()->json([
                'message' => 'Member logged in successfully!',
                'token' => $token,
                'role' => $member->role,
                'id' => $member->id, // إضافة id
                'name' => $member->name, // إضافة name
                'img' => $member->image,
            ]);
        }

        // محاولة تسجيل الدخول كـ User
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $user->tokens()->delete(); // حذف التوكنات القديمة
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'User logged in successfully!',
                'token' => $token,
                'role' => $user->role,
                'id' => $user->id, // إضافة id
                'name' => $user->name, // إضافة name
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday,
                'img' => $user->img,
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                // تسجيل الدخول ناجح

                $user = Auth::user();

                // إنشاء `remember_token` جديد
                $token = Str::random(60);

                // حفظ التوكن في قاعدة البيانات
                $user->remember_token = $token;
                $user->save();

                // إرسال التوكن ككوكيز
                cookie()->queue(cookie('remember_token', $token, 60 * 24 * 30)); // الكوكيز صالح لمدة 30 يوم

                // إذا كانت بيانات الدخول غير صحيحة
                return response()->json(['message' => 'Unauthorized'], 401);

            }
        }
    }

    // دالة التسجيل
    public function register(Request $request)
    {
        // التحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // التأكد من تطابق كلمة المرور
        ]);

        // إذا كانت البيانات المدخلة غير صحيحة
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // إنشاء المستخدم الجديد
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // تأكيد تشفير كلمة المرور
        ]);
        
        // $host = $request->getHost();
        // $subdomain = explode('.', $host)[0];
        // dd($subdomain);
        // if($subdomain=='dashboard'){
        //     $user = User::create([
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'role'=>'user',
        //         'password' => Hash::make($request->password), // تأكيد تشفير كلمة المرور
        //     ]);
        // }else{
        //     $user = User::create([
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'password' => Hash::make($request->password), // تأكيد تشفير كلمة المرور
        //     ]);

        // إنشاء التوكن للمستخدم الجديد
        $token = $user->createToken('auth_token')->plainTextToken;

        // إرجاع التوكن في الاستجابة
        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
        ], 201); // رمز الحالة 201 يعني أن الكائن تم إنشاؤه بنجاح
    }
}