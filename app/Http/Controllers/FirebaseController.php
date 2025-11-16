<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;

class FirebaseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function storeData()
    {
        $data = [
            'name' => 'Salem',
            'age' => 25,
        ];

        $this->firebaseService->set('users/1', $data);
        return response()->json(['message' => 'Data stored successfully']);
    }

    public function fetchData()
    {
        $data = $this->firebaseService->get('users/1');
        return response()->json($data);
    }
}
