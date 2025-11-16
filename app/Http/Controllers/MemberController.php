<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberController extends Controller
{
    public function getMembersByUserId($user_id)
    {
        try {
            // الحصول على الأعضاء المرتبطين بـ admin_id
            $members = Member::where('user_id', $user_id)->get();

            // التحقق إذا كانت النتيجة فارغة
            if ($members->isEmpty()) {
                return response()->json(['message' => 'No members found for this admin.'], 404);
            }

            // إعادة الأعضاء في شكل JSON
            return response()->json(['members' => $members], 200);
        } catch (\Exception $e) {
            // التعامل مع الأخطاء
            return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
        }
    }
}
