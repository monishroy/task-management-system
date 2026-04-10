<?php

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('can list tasks', function () {
    Task::create(['title' => 'Sample Test Task', 'description' => 'Sample description', 'status' => 'pending']);

    $this->get(route('tasks.index'))->assertStatus(200)->assertSee('Sample Test Task');
});

it('can display the creation form', function () {
    $this->get(route('tasks.create'))->assertStatus(200);
});

it('can create a new task', function () {
    $data = [
        'title' => 'New Awesome Task',
        'description' => 'Task details here',
        'status' => 'pending',
    ];

    $this->post(route('tasks.store'), $data)
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', ['title' => 'New Awesome Task']);
});

it('can create a task with attachments', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $data = [
        'title' => 'Task with File',
        'description' => 'Task details here',
        'status' => 'pending',
        'attachments' => [$file],
    ];

    $this->post(route('tasks.store'), $data)
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $task = Task::where('title', 'Task with File')->first();
    $this->assertCount(1, $task->attachments);
    $this->assertEquals('document.pdf', $task->attachments->first()->filename);

    Storage::disk('public')->assertExists($task->attachments->first()->file_path);
});

it('can display a task', function () {
    $task = Task::create(['title' => 'Test Task to View', 'description' => 'Test description', 'status' => 'pending']);

    $this->get(route('tasks.show', $task))
        ->assertStatus(200)
        ->assertSee('Test Task to View');
});

it('can display the edit form', function () {
    $task = Task::create(['title' => 'Test Task to Edit', 'description' => 'Test description', 'status' => 'pending']);

    $this->get(route('tasks.edit', $task))
        ->assertStatus(200)
        ->assertSee('Test Task to Edit');
});

it('can update a task', function () {
    $task = Task::create(['title' => 'Old Title', 'description' => 'Old description', 'status' => 'pending']);

    $data = [
        'title' => 'New Title Updated',
        'description' => 'New description properly updated',
        'status' => 'completed',
    ];

    $this->put(route('tasks.update', $task), $data)
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', ['title' => 'New Title Updated', 'status' => 'completed']);
});

it('can delete a task', function () {
    $task = Task::create(['title' => 'Task to delete', 'description' => 'Test description', 'status' => 'pending']);

    $this->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task deleted successfully.');

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});
