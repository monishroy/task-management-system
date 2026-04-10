@extends('layouts.app')

@section('content')
<div class="mb-6 mx-4 sm:mx-0 flex items-center">
    <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Tasks
    </a>
</div>

<div class="bg-white mx-4 sm:mx-0 shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Task Details</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Full information about the task and its attachments.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tasks.edit', $task) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Edit Task</a>
        </div>
    </div>
    
    <div class="px-4 py-5 sm:p-6">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            <div class="sm:col-span-2 mb-4">
                <dt class="text-sm font-medium text-gray-500">Title</dt>
                <dd class="mt-1 text-lg text-gray-900">{{ $task->title }}</dd>
            </div>
            
            <div class="sm:col-span-1 mb-4">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
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
                </dd>
            </div>
            
            <div class="sm:col-span-1 mb-4">
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->created_at->format('M d, Y h:i A') }}</dd>
            </div>
            
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $task->description ?: 'No description provided.' }}</dd>
            </div>

            <div class="sm:col-span-2 mt-4 pt-6 border-t border-gray-100">
                <dt class="text-sm font-medium text-gray-500 mb-4">Attachments</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($task->attachments->isEmpty())
                        <p class="text-gray-500 italic">No attachments for this task.</p>
                    @else
                        <ul role="list" class="border border-gray-200 rounded-md divide-y divide-gray-200">
                            @foreach($task->attachments as $attachment)
                                <li class="pl-3 pr-4 px-3 py-3 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span class="ml-2 flex-1 w-0 truncate">
                                            {{ $attachment->filename }}
                                        </span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex gap-2 items-center space-x-2">
                                        @if(Str::startsWith($attachment->mime_type, 'image/'))
                                            <button type="button" onclick="openModal('{{ asset('storage/' . $attachment->file_path) }}', '{{ $attachment->filename }}')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                                                View
                                            </button>
                                        @else
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                                                View
                                            </a>
                                        @endif
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors">
                                            Download
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Image Preview</h3>
                        <div class="flex justify-center items-center bg-gray-100 p-4 rounded-lg border border-gray-200 min-h-[50vh]">
                            <img id="modalImage" src="" class="w-auto h-auto max-w-full max-h-[75vh] object-contain drop-shadow-md rounded" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(imageSrc, title) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modal-title').innerText = title;
        document.getElementById('imageModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.getElementById('modalImage').src = "";
    }
</script>
@endsection
