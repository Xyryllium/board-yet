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
        /** @var \App\Models\User $user */
        $user = auth('sanctum')->user();

        $boards = $this->boardService->list($user);

        return response()->json([
            'success' => true,
            'data' => $boards,
            'message' => 'Boards retrieved successfully',
        ], 200);
    }

    public function show(Request $request, int $boardId): JsonResponse
    {
        $organizationId = $request->attributes->get('organization_id');
        $board = $this->boardService->getBoardWithCards($boardId, $organizationId);
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

            /** @var \App\Models\User $user */
            $user = auth('sanctum')->user();

            $board = $this->boardService->create($user, $data);
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

            /** @var \App\Models\User $user */
            $user = auth('sanctum')->user();

            $board = $this->boardService->update(
                $user,
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
