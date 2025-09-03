<?php

namespace App\Http\Controllers;

use App\Application\Column\Services\ColumnService;
use App\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function __construct(private ColumnService $columnService)
    {
    }

    public function index(int $boardId): JsonResponse
    {
        try {
            $columns = $this->columnService->list(auth()->user(), $boardId);

            return response()->json([
                'data' => $columns
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
    }

    public function store(Request $request, int $boardId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:3|max:255',
            ]);

            $data['board_id'] = $boardId;

            $column = $this->columnService->create(auth()->user(), $data);

            return response()->json([
                'message' => 'Column created successfully!',
                'data' => $column
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
    }

    public function update(Request $request, Board $board): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:3|max:255',
                'id' => 'required|integer|exists:columns,id',
                'order' => 'sometimes|integer',
            ]);

            $data['board_id'] = $board->id;

            $column = $this->columnService->update(
                auth()->user(),
                $data,
            );

            return response()->json([
                'message' => 'Column updated successfully!',
                'data' => $column
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
