<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tasks = Task::latest()->paginate(10);
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = Task::create($request->validated());

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $task->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): View
    {
        $task->load('attachments');
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $task->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
