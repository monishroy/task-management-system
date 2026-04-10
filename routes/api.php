<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;

Route::apiResource('tasks', TaskController::class)->names('api.tasks');
