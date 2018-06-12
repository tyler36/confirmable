<?php

namespace Tyler36\ConfirmableTrait\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

/**
 * Class IsConfirmed
 */
class IsConfirmed
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

        if (auth()->user()->isNotConfirmed()) {
            abort_if($request->ajax(), 403);

            return redirect()->route('confirm.update');
        }

        // Continue
        return $next($request);
    }
}
