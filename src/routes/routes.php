<?php

/**
 * These routes handle the basic workflow
 */

// This route creates a new token
Route::group(['middleware' => ['web']], function () {
    Route::get('confirm/resend', [
        'as'   => 'confirm.resend',
        'uses' => '\App\Http\Controllers\Auth\ConfirmUserController@resend',
    ]);

    // This route allows users to enter their email & confirmation
    Route::get('confirm', [
        'as'   => 'confirm.edit',
        'uses' => '\App\Http\Controllers\Auth\ConfirmUserController@edit',
    ]);

    // This route confirms users email & confirmation token
    Route::post('confirm', [
        'as'   => 'confirm.update',
        'uses' => '\App\Http\Controllers\Auth\ConfirmUserController@update',
    ]);
});
