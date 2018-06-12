<?php

namespace App\Http\Controllers\Auth;

use App\Confirmation;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmationFormRequest;
use Tyler36\ConfirmableTrait\Events\UserRequestedConfirmationEmail;
use Tyler36\ConfirmableTrait\Middleware\isNotConfirmed;

class ConfirmUserController extends Controller
{
    /**
     * UserConfirmationController constructor
     */
    public function __construct()
    {
        $this->middleware([isNotConfirmed::class]);
    }

    /**
     * Issue a new Token
     *
     * @return void
     */
    public function resend()
    {
        event(new UserRequestedConfirmationEmail(auth()->user()));

        session()->flash('success', trans('confirmable::message.resent'));

        // REDIRECT:    Member show
        return redirect()->route('confirm.edit');
    }

    /**
     * Display form for confirmation
     *
     * @return void
     */
    public function edit()
    {
        return view('auth.confirmation');
    }

    /**
     * Validate and update confirmation
     *
     * @return void
     */
    public function update(ConfirmationFormRequest $request)
    {
        $confirmation = Confirmation::where('email', auth()->user()->email)
            ->firstOrFail();

        $confirmation->validateToken(request()->token)->validate();

        auth()->user()->markConfirmed();

        session()->flash('success', trans('confirmable::message.confirm.success'));

        return redirect()->route('user.show', $confirmation->user);
    }
}
