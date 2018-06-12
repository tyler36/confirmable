<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Confirmation extends Model
{
    protected $validForHours = 24;

    protected $fillable = ['email'];

    /**
     * RELATIONSHIP:        Confirmation 1:1 User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Check model was updated within valid time period
     *
     * @param string $token Token to validate against
     * @param null   $hours Number of hours the model is valid for
     *
     * @return mixed
     */
    public function validateToken($token, $hours = null)
    {
        $messages = [
            'same'  => trans('confirmable::message.token.mismatch'),
            'after' => trans('confirmable::message.token.expired'),
        ];

        // Convert and added confirmation
        $this->confirm_token = $token;

        return Validator::make($this->toArray(), [
            'token'      => 'same:confirm_token',
            'updated_at' => 'after:'.$this->validAfterTime($hours),
        ], $messages);
    }

    /**
     * @param null $hours
     *
     * @return \Carbon\Carbon
     */
    public function validAfterTime($hours = null)
    {
        return Carbon::now()->subHours($hours || $this->validForHours);
    }
}
