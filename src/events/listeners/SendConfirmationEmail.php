<?php

namespace Tyler36\ConfirmableTrait\Events\Listeners;

use App\Notifications\ConfirmEmailAccount;
use Tyler36\ConfirmableTrait\Events\UserRequestedConfirmationEmail;

class SendConfirmationEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param UserRequestedConfirmationEmail $event
     *
     * @return void
     */
    public function handle(UserRequestedConfirmationEmail $event)
    {
        $confirmation = $event->user->createNewConfirmationToken();

        $event->user->notify(new ConfirmEmailAccount($confirmation));
    }
}
