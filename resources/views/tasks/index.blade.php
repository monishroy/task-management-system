@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mx-4 sm:mx-0 mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Your Tasks</h1>
    <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Task
    </a>
</div>

<div class="bg-white mx-4 sm:mx-0 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
    <div class="p-0 sm:p-6 text-gray-900">
        @if($tasks->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No tasks found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new task.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sm:bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">Task</th>
                            <th scope="col" class="px-6 py-3 text-left">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($tasks as $task)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                    @if($task->description)
                                        <div class="text-sm text-gray-500 max-w-md truncate">{{ $task->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($task->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-2"></span> Pending
                                        </span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span> In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span> Completed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-200 px-3 py-1.5 rounded-md transition-colors">View</a>
                                        <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition-colors">Edit</a>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="mt-4 px-6">
                    {{ $tasks->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
