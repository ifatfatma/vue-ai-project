<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromptController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PROMPT LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Prompt::with(['user', 'category']);

        /*
        |--------------------------------------------------------------------------
        | FILTER BY USER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY DATE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        /*
        |--------------------------------------------------------------------------
        | PENDING FIRST
        |--------------------------------------------------------------------------
        */

        $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 0
                    WHEN status = 'approved' THEN 1
                    WHEN status = 'rejected' THEN 2
                    ELSE 3
                END
            ")
            ->latest();

        $prompts = $query
            ->paginate(10)
            ->withQueryString();

        $users = User::all();

        return view(
            'pages.admin.prompt.index',
            compact('prompts', 'users')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE PROMPT FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $categories = Category::all();

        // Available AI tools ki list
        $availableTools = [
            'ChatGPT' => 'ChatGPT',
            'Claude' => 'Claude',
            'Gemini' => 'Google Gemini',
            'Copilot' => 'Microsoft Copilot',
            'DeepSeek' => 'DeepSeek',
            'Llama' => 'Meta Llama',
        ];

        return view(
            'pages.admin.prompt.create',
            compact('categories', 'availableTools')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE PROMPTS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Admin-created prompts are automatically APPROVED.
    |
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'prompts' => [
                'required',
                'array',
                'min:1',
            ],

            'prompts.*.ai_tool' => [
                'nullable',
                'array',
            ],

            'prompts.*.ai_tool.*' => [
                'string',
                'max:100',
            ],

            'prompts.*.label' => [
                'nullable',
                'string',
                'max:255',
            ],

            'prompts.*.text' => [
                'required',
                'string',
            ],

            'prompts.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | STORE EACH PROMPT
        |--------------------------------------------------------------------------
        */

        foreach ($request->input('prompts', []) as $index => $promptData) {

            $imagePath = null;


            /*
            |--------------------------------------------------------------------------
            | UPLOAD IMAGE
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile("prompts.$index.image")) {

                $imagePath = $request
                    ->file("prompts.$index.image")
                    ->store('prompts', 'public');
            }


            /*
            |--------------------------------------------------------------------------
            | CREATE PROMPT
            |--------------------------------------------------------------------------
            */

            Prompt::create([

                'title' => $request->title,

                'category_id' => $request->category_id,

                'prompt_text' => $promptData['text'],

                'ai_tool' => $promptData['ai_tool'] ?? [],

                'label' => $promptData['label'] ?? null,

                'image' => $imagePath,

                /*
                | Admin is the creator
                */
                'user_id' => auth()->id(),

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | Admin-created prompt is automatically approved.
                |
                */
                'status' => 'approved',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('admin.prompts.create')
            ->with(
                'success',
                'Prompts added successfully and published!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT PROMPT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $prompt = Prompt::findOrFail($id);

        $categories = Category::all();

        return view(
            'pages.admin.prompt.edit',
            compact('prompt', 'categories')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROMPT
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $prompt = Prompt::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'label' => [
                'nullable',
                'string',
                'max:255',
            ],

            'prompt_text' => [
                'required',
                'string',
            ],

            /*
            | Multiple AI tools
            */
            'ai_tool' => [
                'nullable',
                'array',
            ],

            'ai_tool.*' => [
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CURRENT IMAGE
        |--------------------------------------------------------------------------
        */

        $imagePath = $prompt->image;


        /*
        |--------------------------------------------------------------------------
        | REMOVE OLD IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            $request->has('remove_image') &&
            $request->remove_image == 1
        ) {

            if (
                $prompt->image &&
                Storage::disk('public')->exists($prompt->image)
            ) {

                Storage::disk('public')->delete(
                    $prompt->image
                );
            }

            $imagePath = null;
        }


        /*
        |--------------------------------------------------------------------------
        | UPLOAD NEW IMAGE
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            /*
            | Delete old image
            */

            if (
                $prompt->image &&
                Storage::disk('public')->exists($prompt->image)
            ) {

                Storage::disk('public')->delete(
                    $prompt->image
                );
            }


            /*
            | Store new image
            */

            $imagePath = $request
                ->file('image')
                ->store('prompts', 'public');
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PROMPT
        |--------------------------------------------------------------------------
        */

        $prompt->update([

            'category_id' => $request->category_id,

            'title' => $request->title,

            'label' => $request->label,

            'prompt_text' => $request->prompt_text,

            /*
            | Multiple AI tools
            */
            'ai_tool' => $request->input('ai_tool', []),

            'image' => $imagePath,
        ]);


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('admin.prompts.index')
            ->with(
                'success',
                'Prompt updated successfully!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PROMPT
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $prompt = Prompt::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | DELETE IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            $prompt->image &&
            Storage::disk('public')->exists($prompt->image)
        ) {

            Storage::disk('public')->delete(
                $prompt->image
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DELETE PROMPT
        |--------------------------------------------------------------------------
        */

        $prompt->delete();


        return redirect()
            ->back()
            ->with(
                'success',
                'Prompt deleted successfully!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE PROMPT
    |--------------------------------------------------------------------------
    */

    public function approve($id)
    {
        $prompt = Prompt::findOrFail($id);


        $prompt->update([
            'status' => 'approved',
        ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Prompt successfully approved and published!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT PROMPT
    |--------------------------------------------------------------------------
    */

    public function reject($id)
    {
        $prompt = Prompt::findOrFail($id);


        $prompt->update([
            'status' => 'rejected',
        ]);


        return redirect()
            ->back()
            ->with(
                'error',
                'Prompt has been rejected.'
            );
    }


    /*
   
    SHOW PROMPT
    
    */

    public function show($id)
    {
        $prompt = Prompt::with([
            'user',
            'category',
        ])->findOrFail($id);


        return view(
            'pages.admin.prompt.show',
            compact('prompt')
        );
    }
}