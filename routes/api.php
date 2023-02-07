<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post("register", [UserController::class, "register"])->name('register');
Route::post("login", [UserController::class, "login"])->name('login');
Route::get("get_all_books", [BookController::class, "get_all_books"])->name('get_all_books');

Route::get("search_books", [BookController::class, "search_books"])->name('search_books');
Route::post("add_book", [BookController::class, "add_book"])->name('add_book');


Route::group(['middleware' => ['auth:api']], function () {
    Route::post("logout", [UserController::class, "logout"]);
    Route::put("switch_active_status", [UserController::class, "switch_active_status"])->name('switch_active_status');
    Route::get("get_all_users", [UserController::class, "get_all_users"])->name('get_all_users');
    Route::get("getProfile", [UserController::class, "getProfile"])->name('getProfile');
    Route::get("view_uploaded_books", [UserController::class, "view_uploaded_books"])->name('view_uploaded_books');
});
