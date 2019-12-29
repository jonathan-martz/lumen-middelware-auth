<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Model\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class Authenticate
 *
 * @package App\Http\Middleware
 */
class Authenticate extends Controller
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;


    /**
     * Authenticate constructor.
     * @param Auth $auth
     * @param Request $request
     */
    public function __construct(Auth $auth, Request $request)
    {
        $this->auth = $auth;
        parent::__construct($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     * @todo try to replace request variable
     * @todo update version to 0.2
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $validator = Validator::make($request->all(), [
            'auth.username' => 'required',
            'auth.userid' => 'required|integer',
            'auth.token' => 'required|size:512'
        ])->validate();

        $users = DB::table('users')
            ->where('username', '=', $request->input('auth.username'))
            ->where('username_hash', '=', sha1($request->input('auth.username')));

        $user = $users->first();
        if ($user) {
            $user = new User((array)$user);

            if ($user->getActive() === true) {
                $tokens = DB::table('auth_tokens')
                    ->where('UID', '=', $user->getAuthIdentifier())
                    ->where('token', '=', $request->input('auth.token'));
                if ($tokens->count() === 1) {
                    return $next($request);
                } else {
                    $this->addMessage('error', ' doesnt exists.');
                    return $this->getResponse();
                }
            } else {
                $this->addMessage('error', 'User isnt actived yet.');
            }
        } else {
            $this->addMessage('error', 'User doesnt exists.');
        }

        return $this->getResponse();
    }

}
