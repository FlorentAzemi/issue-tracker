<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\IssueMemberController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueTagController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('login',  [LoginController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'login'])->middleware('guest');
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('projects.index'));

    // Projects
    Route::get('projects',                    [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create',             [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects',                   [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}',          [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit',     [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}',          [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}',       [ProjectController::class, 'delete'])->name('projects.delete');
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');

    // Issues (nested under project)
    Route::prefix('projects/{project}/issues')->name('projects.issues.')->group(function () {
        Route::get('/create',                    [IssueController::class, 'create'])->name('create');
        Route::post('/',                         [IssueController::class, 'store'])->name('store');
        Route::get('/{issue}',                   [IssueController::class, 'show'])->name('show');
        Route::get('/{issue}/edit',              [IssueController::class, 'edit'])->name('edit');
        Route::put('/{issue}',                   [IssueController::class, 'update'])->name('update');
        Route::delete('/{issue}',                [IssueController::class, 'delete'])->name('delete');
        Route::post('/{issue}/restore',          [IssueController::class, 'restore'])->name('restore');

        // Comments
        Route::get('/{issue}/comments',              [CommentController::class, 'index'])->name('comments.index');
        Route::post('/{issue}/comments',             [CommentController::class, 'store'])->name('comments.store');
        Route::delete('/{issue}/comments/{comment}', [CommentController::class, 'delete'])->name('comments.delete');

        // Tags
        Route::post('/{issue}/tags/toggle',      [IssueTagController::class, 'toggle'])->name('tags.toggle');

        // Members
        Route::post('/{issue}/members/toggle',   [IssueMemberController::class, 'toggle'])->name('members.toggle');
    });

    // Tags
    Route::get('tags',              [TagController::class, 'index'])->name('tags.index');
    Route::post('tags',             [TagController::class, 'store'])->name('tags.store');
    Route::delete('tags/{tag}',     [TagController::class, 'delete'])->name('tags.delete');
    Route::post('tags/{tag}/restore', [TagController::class, 'restore'])->name('tags.restore');

    // Global issues list + standalone issue view
    Route::get('issues', [IssueController::class, 'index'])->name('issues.index');
    Route::get('issues/{issue}', [IssueController::class, 'showGlobal'])->name('issues.show');
});
