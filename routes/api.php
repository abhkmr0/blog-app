<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\TempImageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    //User releted Api
    Route::post('/logout', [UserController::class, 'logout']);
    //Blog releted Api
    Route::post('/create-blog', [BlogController::class, 'store']);
    Route::get('/blogs-list', [BlogController::class, 'index']);
    Route::get('/single-blog-list/{id}', [BlogController::class, 'singleBlog']);
    Route::post('/updateBlog/{id}', [BlogController::class, 'update']);
    Route::post('/image-upload', [TempImageController::class, 'store']);
    Route::delete('/blog-delete/{id}', [BlogController::class, 'destroy']);
    Route::get('/blog/search', [BlogController::class, 'searchBlog']);
    Route::post('/blog/likes/{id}', [BlogController::class, 'like']);
    Route::post('/blog/comment', [BlogController::class, 'comment']);
    Route::get('/blog/comments/{id}', [BlogController::class, 'showComments']);
    Route::get('/commentCount', [BlogController::class, 'commentCount']);
    
});
