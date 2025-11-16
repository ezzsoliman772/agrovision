<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Apply middleware to ensure the user is authenticated before accessing this controller's methods
    public function __construct()
    {
        //$this->middleware('auth:sanctum');  // This will require a valid Sanctum token
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:15',
            'gender' => 'required|string|in:male,female',
            'role' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for the image

            // You can add more validation rules if necessary, such as role
        ]);

        // Get the currently authenticated user using Sanctum
        $user = auth('sanctum')->user();

        // If no user is authenticated, return an Unauthorized response
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create a new member and associate it with the authenticated user
        $member = new Member();
        $member->name = $request->name;
        $member->email = $request->email;
        $member->password = Hash::make($request->password);
        $member->phone = $request->phone;
        $member->gender = $request->gender;
        $member->role = $request->role;
        $member->user_id = $user->id; // Associate the member with the authenticated user

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('/photos', 'public'); // Save the file in 'storage/app/public/members'
            $member->image = $path;
        }

        $member->save(); // Save the new member to the database

        Auth::guard('member')->login($member);

        // Return the response with the created member's details
        return response()->json([
            'message' => 'Member added successfully',
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'gender' => $member->gender,
                'role' => $member->role,
                'image' => $member->image ? asset('storage/' . $member->image) : null,
            ],
        ]);
    }
    public function update(Request $request, $id)
{
    // التحقق من صلاحيات المستخدم
    if (Auth::user()->role !== 'user') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // التحقق من صحة البيانات المدخلة
    $request->validate([
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:members,email,' . $id,
        'password' => 'nullable|string|min:8',
        'phone' => 'nullable|string|max:15',
        'gender' => 'nullable|string|in:male,female',
        'role' => 'nullable|string',
    ]);

    // إيجاد العضو
    $member = Member::findOrFail($id);

    // تحديث بيانات العضو
    $member->update(array_filter([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'gender' => $request->gender,
        'role' => $request->role,
        'password' => $request->password ? Hash::make($request->password) : null,
    ]));

    return response()->json([
        'message' => 'Member updated successfully',
        'member' => $member,
    ]);
}

    public function destroy($id)
    {
        // التحقق من صلاحيات المستخدم
        if (Auth::user()->role !== 'user') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // إيجاد العضو وحذفه
        $member = Member::findOrFail($id);
        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully',
        ]);
    }

}
