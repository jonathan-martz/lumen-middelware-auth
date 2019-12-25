<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use \Illuminate\Http\Request;

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
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth, Request $request)
    {
        $this->auth = $auth;
        parent::__construct($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $validator = Validator::make($request->all(), [
            'auth.username' => 'required',
            'auth.userid' => 'required|integer',
            'auth.token' => 'required|size:512'
        ])->validate();

        $users = DB::table('users')
            ->where('username','=',$request->input('auth.username'))
            ->where('username_hash','=',sha1($request->input('auth.username')));

        $count = $users->count();

        $user = $users->first();

        if($user->getActive() === true){
            if($count === 1){
                $tokens = DB::table('auth_tokens')
                    ->where('UID','=',$user->id)
                    ->where('token','=',$request->input('auth.token'));
                if($tokens->count() === 1){
                    return $next($request);
                }
                else{
                    $this->addMessage('error',' doesnt exists.');
                    return $this->getResponse();
                }
            }
            else{
                $this->addMessage('error','User doesnt exists.');
                return $this->getResponse();
            }
        }
        else{
            $this->addMessage('error','User isnt actived yet.');
        }
    }

}
