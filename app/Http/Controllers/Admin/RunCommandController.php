<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RunCommand;
use Illuminate\Http\Request;

class RunCommandController extends Controller
{
    /**
     * Display saved SQL commands.
     */
    public function index()
    {
        $savedCommands = RunCommand::latest()->get();

        return view('pages.admin.run-commands', compact('savedCommands'));
    }

    /**
     * Save a SQL command.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'heading' => 'required|string|max:255',
            'command_sql' => 'required|string',
        ]);

        $command = new RunCommand();
        $command->heading = $validated['heading'];
        $command->command_sql = $validated['command_sql'];
        $command->save();

        return redirect()
            ->route('admin.commands.index')
            ->with('success', 'Command / SQL saved successfully!');
    }
}
