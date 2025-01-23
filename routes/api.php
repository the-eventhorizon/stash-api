<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ShoppinglistController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AuthController;
use App\Models\Household;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Handle everything household related
    Route::post('/households', [HouseholdController::class, 'store']);
    Route::get('/households', [HouseholdController::class, 'index']);
    Route::post('/households/{household}/invite', [HouseholdController::class, 'invite']);
    Route::patch('/households/{household}/leave', [HouseholdController::class, 'leave']);
    Route::patch('/households/{household}/remove/user/{user}', [HouseholdController::class, 'removeUser']);
    Route::patch('/households/{household}', [HouseholdController::class, 'update']);
    Route::delete('/households/{household}', [HouseholdController::class, 'destroy']);

    // Handle everything shoppinglist related
    Route::post('/households/{household}/shoppinglists', [ShoppinglistController::class, 'store']);
    Route::get('/households/{household}/shoppinglists', [ShoppinglistController::class, 'index']);
    Route::get('/households/{household}/shoppinglists/{shoppinglist}', [ShoppinglistController::class, 'show']);
    Route::patch('/households/{household}/shoppinglists/{shoppinglist}', [ShoppinglistController::class, 'update']);
    Route::delete('/households/{household}/shoppinglists/{shoppinglist}', [ShoppinglistController::class, 'destroy']);

    // Handle everything item related
    Route::post('/households/{household}/shoppinglists/{shoppinglist}/items', [ItemController::class, 'store']);
    Route::get('/households/{household}/shoppinglists/{shoppinglist}/items', [ItemController::class, 'index']);
    Route::patch('/households/{household}/shoppinglists/{shoppinglist}/items/{item}/check', [ItemController::class, 'toggleCheck']);
    Route::patch('/households/{household}/shoppinglists/{shoppinglist}/items/{item}', [ItemController::class, 'update']);
    Route::delete('/households/{household}/shoppinglists/{shoppinglist}/items/{item}', [ItemController::class, 'destroy']);

    // Handle everything join request related
    Route::post('/households/public', [HouseholdController::class, 'getPublicUserHouseholds']);
    Route::post('/households/{household}/join-request', [HouseholdController::class, 'requestToJoin']);
    Route::get('/user/requests', [HouseholdController::class, 'showAllJoinRequests']);
    Route::get('/user/households/{household}/join-requests', [HouseholdController::class, 'showHouseholdJoinRequests']);
    Route::patch('/user/households/{household}/join-requests/{householdRequest}', [HouseholdController::class, 'respondToJoinRequests']);

    // Handle everything invitation related
    Route::post('/households/{household}/invite-user', [HouseholdController::class, 'inviteUser']);
    Route::get('/user/invitations', [HouseholdController::class, 'showInvitations']);
    Route::patch('/user/invitations/{invitation}', [HouseholdController::class, 'respondToInvitation']);

    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user/{user}', [AuthController::class, 'destroy']);
});
