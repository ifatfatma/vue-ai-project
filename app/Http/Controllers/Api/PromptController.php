<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    /**
     * Get approved prompts with category, image URL and pagination.
     */
    public function index(Request $request)
    {
        $perPage = min(
            max((int) $request->query('per_page', 12), 1),
            50
        );

        $prompts = Prompt::with('category')
            ->where('status', 'approved')
            ->latest()
            ->paginate($perPage);

        $data = $prompts->getCollection()->map(function ($prompt) {
            $imageUrl = null;

            if (!empty($prompt->image)) {
                $imagePath = ltrim($prompt->image, '/');

                $imageUrl = str_starts_with($imagePath, 'http://')
                    || str_starts_with($imagePath, 'https://')
                    ? $imagePath
                    : asset('storage/' . $imagePath);
            }

            return [
                'id' => $prompt->id,
                'title' => $prompt->title,
                'label' => $prompt->label,
                'prompt_text' => $prompt->prompt_text,

                'category' => $prompt->category ? [
                    'id' => $prompt->category->id,
                    'name' => $prompt->category->name,
                    'slug' => $prompt->category->slug,
                ] : null,

                'ai_tool' => $prompt->ai_tool,
                'image' => $prompt->image,
                'image_url' => $imageUrl,
                'copies_count' => $prompt->copies_count,
                'created_at' => $prompt->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Prompts fetched successfully.',
            'data' => $data,
            'pagination' => [
                'current_page' => $prompts->currentPage(),
                'last_page' => $prompts->lastPage(),
                'per_page' => $prompts->perPage(),
                'total' => $prompts->total(),
                'next_page_url' => $prompts->nextPageUrl(),
                'previous_page_url' => $prompts->previousPageUrl(),
            ],
        ]);
    }
}
