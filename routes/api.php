<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Queue\BookingAdminController;
use App\Http\Controllers\Queue\BookingController;
use App\Http\Controllers\Queue\TicketAdminController;
use App\Http\Controllers\Queue\TicketController;
use App\Http\Controllers\Settings\CalendarSettingController;
use App\Http\Controllers\Settings\QueueSettingController;
use App\Http\Controllers\Settings\SettingStatusController;
use App\Http\Controllers\Settings\TicketGroupController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

// Public Route
Route::prefix('auth')->controller(AuthController::class)->group(function () {

    Route::post('login', 'login');
    Route::get('logout', 'logout');
});
Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('register', 'store');
});


// LINE Liff Route
Route::middleware('lineLogin')->prefix('queue')->group(function () {

    Route::controller(TicketController::class)->prefix('ticket')->group(function () {
        Route::post('generate', 'generateTicket');
        Route::post('group_by_code', 'getTicketGroupByCode');
        Route::get('my', 'currentTicket');
    });

    Route::controller(BookingController::class)->prefix('booking')->group(function () {
        Route::get('my', 'my_booking');
        Route::get('header', 'getHeader');
        Route::get('calendar/{year}/{month}', 'getCalendarDetail');
        Route::post('register', 'store');
        // Route::post('update/{bookingId}', 'update');
        Route::get('cancel/{bookingId}', 'bookingCancel');
        Route::get('detail/{bookingId}', 'show');
    });
});


// Private Route
Route::middleware('auth:sanctum')->group(function () {
    // Route::apiResource('line_config', LineConfigController::class);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'update']);

    Route::prefix('admin/setting')->group(function () {

        Route::controller(QueueSettingController::class)->group(function () {
            Route::get('queue', 'show');
            Route::post('queue', 'store');
            Route::put('queue', 'update');
        });

        Route::controller(CalendarSettingController::class)->group(function () {
            Route::get('calendar', 'index');
            Route::get('calendar/{id}', 'show');
            Route::post('calendar', 'store');
            Route::put('calendar/{id}', 'update');
            Route::get('calendarActivate/{id}', 'calendarActivate');
        });

        Route::controller(TicketGroupController::class)->group(function () {
            Route::get('ticket', 'index');
            Route::get('ticket/{id}', 'show');
            Route::post('ticket', 'store');
            Route::put('ticket/{id}', 'update');
            Route::delete('ticket/{id}', 'destroy');
            Route::get('ticketGroupActive/{id}', 'ticketGroupActive');
            Route::get('ticketGroupInactive/{id}', 'ticketGroupInactive');
        });

        Route::get("check", [SettingStatusController::class, "checkAllSettingStatus"]);
    });

    Route::controller(TicketAdminController::class)->prefix('admin/ticket')->group(function () {
        Route::get('groups', 'getQueueGroup');
        Route::get('status', 'getTicketStatus');
        Route::get('next/{ticketGroupId}', 'callNextQueue');
        Route::get('recall/{ticketId}', 'recallQueue');
        Route::get('execute/{ticketId}', 'executeQueue');
        Route::get('postpone/{ticketId}', 'postponeQueue');
        Route::get('reject/{ticketId}', 'rejectQueue');

        Route::get('ticket_list/{ticketGroupId}', 'getAllQueue');
        Route::get('waiting_list/{ticketGroupId}', 'getWaitingQueue');
    });

    Route::controller(BookingAdminController::class)->prefix('admin/booking')->group(function () {
        Route::get('confirm/{bookingId}', 'confirmBooking');
        Route::get('reject/{bookingId}', 'rejectBooking');
        Route::post('revise/{bookingId}', 'reviseBooking');
        Route::get('complete/{bookingId}', 'completeBooking');
        Route::post('check_booking', 'getBookingByCode');
        Route::get('detail/{bookingId}', 'show');
    });
});
