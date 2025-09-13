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

    public function index(): JsonResponse
    {
        $boards = $this->boardService->list(auth()->user());

        return response()->json([
            'success' => true,
            'data' => $boards,
            'message' => 'Boards retrieved successfully',
        ], 200);
    }

    public function show(int $boardId): JsonResponse
    {
        $board = $this->boardService->getBoardWithCards($boardId);
        return response()->json([
            'success' => true,
            'data' => $board,
            'message' => 'Board retrieved successfully'
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:3|max:255'
            ]);

            $board = $this->boardService->create(auth()->user(), $data);
            return response()->json([
                'success' => true,
                'data' => $board,
                'message' => 'Board created successfully!'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, int $boardId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:3|max:255'
            ]);

            $board = $this->boardService->update(
                auth()->user(),
                $data,
                $boardId
            );

            return response()->json([
                'success' => true,
                'data' => $board,
                'message' => 'Board updated successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
