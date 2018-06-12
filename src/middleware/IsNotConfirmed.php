<?php

namespace Tyler36\ConfirmableTrait\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

/**
 * Class IsNotConfirmed
 */
class IsNotConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guest()) {
            throw new AuthenticationException();
        }

        if (auth()->user()->isConfirmed()) {
            $message = trans('confirmable.exists');
            session()->flash('message', $message);

            return (!$request->ajax())
                ? redirect()->route('user.show', auth()->user())
                : response()->json([
                    'errors'  => true,
                    'message' => $message,
                ], 400);
        }

        // Continue
        return $next($request);
    }
}
