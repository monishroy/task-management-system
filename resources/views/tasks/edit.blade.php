@extends('layouts.app')

@section('content')
<div class="mb-6 mx-4 sm:mx-0 flex items-center">
    <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Tasks
    </a>
</div>

<div class="bg-white mx-4 sm:mx-0 shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-100 bg-gray-50">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Task</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Update the details or status of the task.</p>
    </div>
    
    <div class="p-6">
        <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <div class="mt-1">
                        <input type="text" name="title" id="title" class="shadow-sm border focus:ring-indigo-500 py-2 px-3 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('title') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror" value="{{ old('title', $task->title) }}" required>
                    </div>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="mt-1">
                        <textarea id="description" name="description" rows="4" class="shadow-sm py-2 px-3 border focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('description') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror">{{ old('description', $task->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-2 space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                        <div class="flex items-center">
                            <input id="status_pending" name="status" type="radio" value="pending" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('status', $task->status) === 'pending' ? 'checked' : '' }}>
                            <label for="status_pending" class="ml-3 block text-sm font-medium text-gray-700">
                                Pending
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="status_in_progress" name="status" type="radio" value="in_progress" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('status', $task->status) === 'in_progress' ? 'checked' : '' }}>
                            <label for="status_in_progress" class="ml-3 block text-sm font-medium text-gray-700">
                                In Progress
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="status_completed" name="status" type="radio" value="completed" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('status', $task->status) === 'completed' ? 'checked' : '' }}>
                            <label for="status_completed" class="ml-3 block text-sm font-medium text-gray-700">
                                Completed
                            </label>
                        </div>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Attachments</label>
                    <div class="mt-1 flex flex-col gap-2">
                        @if($task->attachments->isNotEmpty())
                            <p class="text-sm text-gray-600 mb-2">Current files: {{ $task->attachments->pluck('filename')->join(', ') }}</p>
                        @endif
                        <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt" class="shadow-sm border focus:ring-indigo-500 py-2 px-3 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('attachments') border-red-300 @enderror @error('attachments.*') border-red-300 @enderror">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Upload new files. Note: This will add to existing files.</p>
                    @error('attachments')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('attachments.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('tasks.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancel</a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
