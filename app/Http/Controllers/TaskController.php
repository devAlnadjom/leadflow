<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'lead_record_id' => 'nullable|exists:lead_records,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $task = new Task();
        $task->user_id = auth()->id();
        $task->title = $validated['title'];
        $task->description = $validated['description'];
        $task->due_date = collect($validated)->has('due_date') && $validated['due_date'] ? \Carbon\Carbon::parse($validated['due_date']) : null;
        $task->lead_record_id = collect($validated)->get('lead_record_id');
        $task->client_id = collect($validated)->get('client_id');
        $task->save();

        return back();
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $task->update([
            'is_completed' => $validated['is_completed'],
        ]);

        return back();
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back();
    }
}
