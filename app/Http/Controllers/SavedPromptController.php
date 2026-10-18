<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;

class SavedPromptController extends Controller
{
    /**
     * Save / unsave a prompt.
     */
    public function toggle(Prompt $prompt)
    {
        $user = auth('frontend')->user();

       
        if (!$user) {
            return response()->json([
                'saved' => false,
                'message' => 'Please login first.',
            ], 401);
        }

        $saved = $user->savedPrompts()
            ->where('prompt_id', $prompt->id)
            ->exists();

        if ($saved) {

            $user->savedPrompts()->detach($prompt->id);

            return response()->json([
                'saved' => false,
                'message' => 'Prompt removed from saved list.',
            ]);
        }

        $user->savedPrompts()->attach($prompt->id);

        return response()->json([
            'saved' => true,
            'message' => 'Prompt saved successfully.',
        ]);
    }

    /**
     * Show user's saved prompts.
     */
    public function index()
    {
        $user = auth('frontend')->user();

       
        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login first.');
        }

        $prompts = $user->savedPrompts()
            ->with('category')
            ->latest('saved_prompts.created_at')
            ->paginate(12);

        return view(
            'pages.front.saved-prompts',
            compact('prompts')
        );
    }
}