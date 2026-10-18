<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Prompt;
use App\Models\PromptCopy;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_prompts'    => Prompt::count(),
            'total_categories' => Category::count(),
            'total_users'      => User::count(),
            'total_copies'     => Schema::hasColumn('prompts', 'copies_count') ? Prompt::sum('copies_count') : 0,
        ];

        $recentPrompts = Prompt::with('category')->latest()->take(5)->get();

        return view('pages.admin.dashboard', compact('stats', 'recentPrompts'));
    }

    // Date-wise Analytics Graph Page
    public function copyAnalytics()
    {
        $analytics = PromptCopy::select(
            DB::raw('copied_date as date'),
            DB::raw('count(*) as total')
        )
        ->groupBy('copied_date')
        ->orderBy('copied_date', 'ASC')
        ->take(30)
        ->get();

        $dates = $analytics->pluck('date')->toArray();
        $counts = $analytics->pluck('total')->toArray();

        // Empty graph prevention check
        if (empty($dates)) {
            $dates = [now()->format('Y-m-d')];
            $counts = [0];
        }

        return view('pages.admin.analytics', compact('dates', 'counts'));
    }
}