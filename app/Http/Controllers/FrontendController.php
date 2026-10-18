<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FrontendController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FRONTEND HOME
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Prompt::with('category')
            ->where('status', 'approved');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('prompt_text', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $prompts = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('home', compact('prompts', 'categories'));
    }


    /*
    |--------------------------------------------------------------------------
    | FRONTEND ADD PROMPT
    |--------------------------------------------------------------------------
    */

    public function storePrompt(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'prompt_text' => 'required|string',
            'ai_tools' => 'nullable|array',
            'ai_tools.*' => 'string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $frontendUser = Auth::guard('frontend')->user();

        if (!$frontendUser) {
            return redirect()
                ->route('frontend.login')
                ->with('error', 'Please login to submit a prompt.');
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('prompts', 'public');
        }

        Prompt::create([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'prompt_text' => $request->prompt_text,
            'ai_tool' => $request->input('ai_tools', []),
            'label' => null,
            'image' => $imagePath,
            'frontend_user_id' => $frontendUser->id,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('user.prompts')
            ->with(
                'success',
                'Prompt submitted successfully! It is waiting for admin approval.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH SUGGESTIONS
    |--------------------------------------------------------------------------
    */

    public function searchSuggestions(Request $request)
    {
        $query = $request->get('query', '');

        $prompts = Prompt::with('category')
            ->where('status', 'approved')
            ->where('title', 'like', '%' . $query . '%')
            ->limit(5)
            ->get();

        return response()->json($prompts);
    }


    /*
    |--------------------------------------------------------------------------
    | USER PROMPT SEARCH SUGGESTIONS
    |--------------------------------------------------------------------------
    */

    public function userSearchSuggestions(Request $request)
    {
        $frontendUser = Auth::guard('frontend')->user();

        if (!$frontendUser) {
            return response()->json([]);
        }

        $query = $request->get('query', '');

        $prompts = Prompt::where(
            'frontend_user_id',
            $frontendUser->id
        )
            ->where('title', 'like', '%' . $query . '%')
            ->limit(5)
            ->get(['id', 'title']);

        return response()->json($prompts);
    }


    /*
    |--------------------------------------------------------------------------
    | CONTACT US
    |--------------------------------------------------------------------------
    */

    public function contactUs()
    {
        return view('pages.front.contact');
    }


    /*
    |--------------------------------------------------------------------------
    | SUBMIT CONTACT
    |--------------------------------------------------------------------------
    */

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        return back()->with(
            'success',
            'Thank you! Your message has been sent successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MY PROMPTS
    |--------------------------------------------------------------------------
    */

    public function myPrompts(Request $request)
    {
        $frontendUser = Auth::guard('frontend')->user();

        if (!$frontendUser) {
            return redirect()->route('frontend.login');
        }

        $query = Prompt::with('category')
            ->where('frontend_user_id', $frontendUser->id);

        if ($request->filled('search')) {
            $query->where(
                'title',
                'like',
                '%' . $request->search . '%'
            );
        }

        $prompts = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 0
                    ELSE 1
                END
            ")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.front.user-prompts',
            compact('prompts')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT PROMPT
    |--------------------------------------------------------------------------
    */

    public function editPrompt($id)
    {
        $frontendUser = Auth::guard('frontend')->user();

        $prompt = Prompt::where('id', $id)
            ->where('frontend_user_id', $frontendUser->id)
            ->firstOrFail();

        $categories = Category::all();

        return view(
            'pages.front.edit-prompt',
            compact('prompt', 'categories')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROMPT
    |--------------------------------------------------------------------------
    */

    public function updatePrompt(Request $request, $id)
    {
        $frontendUser = Auth::guard('frontend')->user();

        $prompt = Prompt::where('id', $id)
            ->where('frontend_user_id', $frontendUser->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'prompt_text' => 'required|string',
            'ai_tool' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            'category_id' => $request->category_id,
            'prompt_text' => $request->prompt_text,
            'ai_tool' => $request->ai_tool,
            'status' => 'pending',
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('prompts', 'public');

            $data['image'] = $imagePath;
        }

        $prompt->update($data);

        return redirect()
            ->route('user.prompts')
            ->with(
                'success',
                'Prompt updated successfully and sent for review!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE FRONTEND PROMPT
    |--------------------------------------------------------------------------
    */

    public function destroyPrompt($id)
    {
        $frontendUser = Auth::guard('frontend')->user();

        $prompt = Prompt::where('id', $id)
            ->where('frontend_user_id', $frontendUser->id)
            ->firstOrFail();

        $prompt->delete();

        return redirect()
            ->route('user.prompts')
            ->with(
                'success',
                'Prompt moved to trash successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW APPROVED PROMPT
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $prompt = Prompt::where('status', 'approved')
            ->findOrFail($id);

        return view('prompts.show', compact('prompt'));
    }
}
