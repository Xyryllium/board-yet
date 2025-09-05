<?php

namespace App\Http\Controllers;

use RuntimeException;
use App\Application\Card\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function __construct(private readonly CardService $cardService)
    {
    }

    public function index(int $columnId): JsonResponse
    {
        $cards = $this->cardService->getCardsByColumnId($columnId);
        return response()->json([
            'data' => $cards,
        ]);
    }

    public function store(Request $request, int $columnId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|min:1|max:255',
                'description' => 'nullable|string',
                'order' => 'required|integer',
            ]);

            $data['column_id'] = $columnId;

            $card = $this->cardService->createCard($data);

            return response()->json([
                'message' => 'Card created successfully!',
                'data' => $card->toArray(),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, int $cardId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'sometimes|required|string|min:1|max:255',
                'description' => 'sometimes|nullable|string',
                'order' => 'sometimes|required|integer',
            ]);

            $card = $this->cardService->updateCard($cardId, $data);

            return response()->json([
                'message' => 'Card updated successfully!',
                'data' => $card->toArray(),
            ], 200);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $cardId): JsonResponse
    {
        try {
            $this->cardService->deleteCard($cardId);
            return response()->json(['message' => 'Card deleted successfully!'], 200);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
