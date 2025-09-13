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

    public function store(Request $request): JsonResponse
    {
        try {
            if ($this->isBulkRequest($request)) {
                return $this->storeBulkColumns($request);
            }

            return $this->storeSingleColumn($request);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
    }

    public function update(Request $request, int $columnId): JsonResponse
    {
        try {
            $data = $request->validate([
                'boardId' => 'required|integer|exists:boards,id',
                'name' => 'required|string|min:3|max:255',
                'order' => 'sometimes|integer',
            ]);

            $data['id'] = $columnId;

            $updatedColumn = $this->columnService->update(
                auth()->user(),
                $data,
            );

            return response()->json([
                'message' => 'Column updated successfully!',
                'data' => $updatedColumn
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    private function isBulkRequest(Request $request): bool
    {
        return $request->has('columns') && is_array($request->columns);
    }

    private function storeBulkColumns(Request $request): JsonResponse
    {
        $data = $request->validate([
            'boardId' => 'required|integer|exists:boards,id',
            'columns' => 'required|array|min:1',
            'columns.*.name' => 'required|string|min:3|max:255',
            'columns.*.order' => 'sometimes|integer|min:0',
        ]);

        $columnsData = $this->prepareBulkColumnsData($data['columns'], $data['boardId']);
        $columns = $this->columnService->createBulk(auth()->user(), $columnsData);

        return response()->json([
            'message' => 'Columns created successfully!',
            'data' => $columns
        ], 201);
    }

    private function storeSingleColumn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'boardId' => 'required|integer|exists:boards,id',
            'name' => 'required|string|min:3|max:255',
        ]);

        $column = $this->columnService->create(auth()->user(), $data);

        return response()->json([
            'message' => 'Column created successfully!',
            'data' => $column
        ], 201);
    }

    private function prepareBulkColumnsData(array $columns, int $boardId): array
    {
        return array_map(function ($column) use ($boardId) {
            return [
                'name' => $column['name'],
                'order' => $column['order'] ?? null,
                'boardId' => $boardId,
            ];
        }, $columns);
    }
}
