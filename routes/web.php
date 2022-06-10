<?php

use App\Http\Controllers\Queue\BookingController;
use App\Http\Controllers\Settings\TicketGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    if(env("FRONT_URL")) {
        return redirect(env("FRONT_URL"));
    } else {
        return response("",404);
    }
});

Route::get('/booking_code/{bookingCode}.svg', [BookingController::class, 'getBookingQRCode']);

Route::get('/ticket_group_code/{ticketGroupCode}.svg', [TicketGroupController::class, 'getTicketGroupQRCode']);
