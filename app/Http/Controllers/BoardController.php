<?php

namespace App\Http\Controllers;

use App\Application\Board\Services\BoardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function __construct(private BoardService $boardService)
    {
        
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:3|max:255'
            ]);

            $this->boardService->create(auth()->user(), $data);
            return response()->json(['message' => 'Board created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}