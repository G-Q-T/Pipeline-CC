<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MetricController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pipeline Content
    Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
    Route::get('/contents/kanban', [ContentController::class, 'kanban'])->name('contents.kanban');
    Route::get('/contents/create', [ContentController::class, 'create'])->name('contents.create');
    Route::post('/contents', [ContentController::class, 'store'])->name('contents.store');
    Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');
    Route::get('/contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
    Route::put('/contents/{content}', [ContentController::class, 'update'])->name('contents.update');
    Route::delete('/contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');

    // Content Status & Publication
    Route::post('/contents/{content}/status', [ContentController::class, 'updateStatus'])->name('contents.status.update');
    Route::post('/contents/{content}/publish', [ContentController::class, 'publish'])->name('contents.publish');

    // Editor Assignments
    Route::post('/contents/{content}/assignments', [ContentController::class, 'assignEditor'])->name('contents.assignments.store');
    Route::delete('/contents/{content}/assignments/{editor}', [ContentController::class, 'removeEditor'])->name('contents.assignments.destroy');

    // Checklists
    Route::post('/contents/{content}/checklists', [ContentController::class, 'storeChecklist'])->name('contents.checklists.store');
    Route::patch('/checklists/{checklist}/toggle', [ContentController::class, 'toggleChecklist'])->name('checklists.toggle');
    Route::delete('/checklists/{checklist}', [ContentController::class, 'destroyChecklist'])->name('checklists.destroy');
    // Metrics & Analytics
    Route::get('/metrics', [MetricController::class, 'index'])->name('metrics.index');
    Route::post('/contents/{content}/metrics', [MetricController::class, 'store'])->name('contents.metrics.store');
    Route::delete('/metrics/{metric}', [MetricController::class, 'destroy'])->name('metrics.destroy');

    // Channels (Creator & Admin only)
    Route::middleware('role:creator,admin')->group(function () {
        Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
        Route::get('/channels/create', [ChannelController::class, 'create'])->name('channels.create');
        Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
        Route::get('/channels/{channel}/edit', [ChannelController::class, 'edit'])->name('channels.edit');
        Route::put('/channels/{channel}', [ChannelController::class, 'update'])->name('channels.update');
        Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');
    });

    // Admin User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.role.update');
    });
});
