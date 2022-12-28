<?php

use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/send-message', function (Request $request) {
    $since = Carbon::parse(Carbon::now()->yesterday()->format('Y-m-d') . ' 08:29:59')->format('Y-m-d H:i:s');
    $until = Carbon::parse(date('Y-m-d') . ' 08:30:00')->format('Y-m-d H:i:s');
    $paidAmount = InvoicePayment::where('created_at', '>=', $since)
        ->where('created_at', '<=', $until)
        ->groupBy('payment_mean_id')
        ->selectRaw('sum(amount) as amount, payment_mean_id')
        ->get();
    dd($paidAmount);
    $data = array(
        "sender" => 'PMP',
        "recipients" => "0781625173,0791923312",
        "message" => "Mwaramutse, ",
    );

    $url = "https://www.intouchsms.co.rw/api/sendsms/.json";
    $data = http_build_query($data);
    $username = "turere.ibibondo";
    $password = "turere.ibibondo";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo $result;
});
