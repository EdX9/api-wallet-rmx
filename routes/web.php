<?php

use App\Models\User;
use App\Models\Wallet\Wallet;
use App\Services\Lock\lockService;
use App\Services\Math\MathService;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

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
    return view('welcome');
});



Route::get('/saldo', function () {

    //$value = Illuminate\Support\Facades\Cache::store('redis')->rememberForever('saldo234', function () {
    //    echo 'cache';
    //    return \App\Models\Wallet\Wallet::find(1)->first();
    //});
    //Illuminate\Support\Facades\Cache::flush();
    //echo 'flush';
    ////$value = Illuminate\Support\Facades\Cache::store('redis')->remember('saldo', 900, function () {
    ////    return \App\Models\Wallet\Wallet::find(1)->first();
    ////});
    //dd($value->balance);

    return  User::find(1)->getWallet('wallet-recargas')->getBalance();  
});




Route::get('/drop',function ()
{
    $tf=\App\Models\Wallet\Transfer::all();
        foreach ($tf as $data) {
            $data->delete();
        }
        $ts=\App\Models\Wallet\Transaction::all();
        foreach ($ts as $data) {
            $data->delete();
        }
        $wa=\App\Models\Wallet\Wallet::all();
        foreach ($wa as $data) {
            $data->delete();
        }
});

Route::get('/deposit',function ()
{
    return User::find(1)->getWallet('wallet-recargas')->deposit('15.5')->uuid;
});
Route::get('/withdraw',function ()
{
    return User::find(1)->getWallet('wallet-recargas')->withdraw('15.5')->uuid;
}); 
Route::get('/deposit/pending',function ()
{
    return User::find(1)->getWallet('wallet-recargas')->pendingDeposit('15.5')->uuid;
});//32f7cdab-ba88-4e62-be3e-fb8ae14c78c9
Route::get('/withdraw/force',function ()
{
    return User::find(1)->getWallet('wallet-recargas')->forceWithdraw('15.5')->uuid;
}); 
Route::get('/balance/update',function ()
{
    return User::find(1)->getWallet('wallet-recargas')->updateBalance();
}); 
Route::get('/transfer',function ()
{
    $walletReceiver = Wallet::where('id',12)->first();
    return User::find(1)->getWallet('wallet-recargas')->transfer($walletReceiver, '10')->uuid;
});
Route::get('/transfer/force',function ()
{
    $walletReceiver = Wallet::where('id',12)->first();
    return User::find(1)->getWallet('wallet-recargas')->transferForce($walletReceiver, '10')->uuid;
});

Route::get('/wallet/update/balance',function ()
{

    $wallet = User::find(1)->getWallet('wallet-recargas');
    $wallet->balance = 0;
    $wallet->save();
    $wallet->updateBalance();
    return $wallet->getBalance();
});
Route::get('/wallet/create',function ()
{
    return User::find(1)->createWallet(
        'Nombre',
        'wallet-Slug',
        'Description',
        2,
        ['meta'=>[1,2,3]]);
}); 