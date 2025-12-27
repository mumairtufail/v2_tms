<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManifestTaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'manifest_full_id'  => 'required',
            'type'              => 'required|string',
            'task_start_date'   => 'required|date',
            'task_start_time'   => 'required',
            'task_end_date'     => 'required|date',
            'task_end_time'     => 'required',
            'assignee'          => 'nullable|string',
            'trailer_id'        => 'nullable|string',
            'security_id'       => 'nullable|string',
            'hours'             => 'nullable|integer',
            'notes'             => 'nullable|string',
            // If using file upload:
            'doc'               => 'nullable|file'
        ]);
        
        $task = new Task();
        $task->type             = $validated['type'];
        $task->task_start_date  = $validated['task_start_date'];
        $task->task_start_time  = $validated['task_start_time'];
        $task->task_end_date    = $validated['task_end_date'];
        $task->task_end_time    = $validated['task_end_time'];
        $task->assignee         = $validated['assignee'] ?? null;
        $task->trailer_id       = $validated['trailer_id'] ?? null;
        $task->security_id      = $validated['security_id'] ?? null;
        $task->hours            = $validated['hours'] ?? null;
        $task->notes            = $validated['notes'] ?? null;
        
        if ($request->hasFile('doc')) {
            $file = $request->file('doc');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('tasks', $filename, 'public');
            $task->doc = $filename;
        }
        
        // Assuming manifest_full_id contains the manifest id
        $task->manifest_id = $validated['manifest_full_id'];
        
        $task->save();
        
        return back()->with('success', 'Task added successfully');
    }

    public function update(Request $request, $manifestId, $taskId)
    {
        $validated = $request->validate([
            'type'              => 'required|string',
            'task_start_date'   => 'required|date',
            'task_start_time'   => 'required',
            'task_end_date'     => 'required|date',
            'task_end_time'     => 'required',
            'assignee'          => 'nullable|string',
            'trailer_id'        => 'nullable|string',
            'security_id'       => 'nullable|string',
            'hours'             => 'nullable|integer',
            'notes'             => 'nullable|string',
            'doc'               => 'nullable|file'
        ]);

        $task = Task::findOrFail($taskId);
        $task->update($validated);

        if ($request->hasFile('doc')) {
            // Delete old file if exists
            if ($task->doc) {
                Storage::disk('public')->delete('tasks/' . $task->doc);
            }
            
            $file = $request->file('doc');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('tasks', $filename, 'public');
            $task->doc = $filename;
            $task->save();
        }

        return back()->with('success', 'Task updated successfully');
    }

    public function destroy($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        // Delete associated file if exists
        if ($task->doc) {
            Storage::disk('public')->delete('tasks/' . $task->doc);
        }
        
        $task->delete();
        
        return response()->json(['success' => true]);
    }
}
