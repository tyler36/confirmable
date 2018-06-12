<?php

namespace Tyler36\ConfirmableTrait;

use App\Confirmation;

trait Confirmable
{
    /**
     * Check if model is confirmed
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return (bool) $this->confirmed;
    }

    /**
     * Check if model is not confirmed
     *
     * @return bool
     */
    public function isNotConfirmed()
    {
        return (bool) !$this->confirmed;
    }

    /**
     * RELATIONSHIP:        Model 1:1 Confirmation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function confirmation()
    {
        return $this->hasOne(Confirmation::class, 'email', 'email');
    }

    /**
     * Mark the model as confirmed
     *
     * @return void
     */
    public function markConfirmed()
    {
        $this->confirmed = true;
        $this->save();
    }

    /**
     * @return mixed
     */
    public function createNewConfirmationToken()
    {
        $confirmation        = Confirmation::firstOrNew(['email' => $this->email]);
        $confirmation->token = str_random(40);

        return $this->confirmation()->save($confirmation);
    }
}
