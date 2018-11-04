<?php

	namespace App\Http\Middleware;

	use App\Http\Controllers\Controller;
	use App\User;
	use Closure;
	use Illuminate\Contracts\Auth\Factory as Auth;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Support\Facades\DB;

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
		public function __construct(Auth $auth)
		{
			$this->auth = $auth;
			parent::__construct();
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
				'username' => 'required',
				'token' => 'required|size:512'
			])->validate();

			$users = DB::connection('mysql.write')
					   ->table('users')
					   ->where('username','=',$request->input('username'))
					   ->where('username_hash','=',sha1($request->input('username')));

			$count = $users->count();

			$user = $users->first();

			if($count === 1){
				$tokens = DB::connection('mysql.write')
							->table('auth_tokens')
							->where('UID','=',$user->id)
							->where('token','=',$request->input('token'));
				if($tokens->count() === 1){
					return $next($request);
				}
				else{
					$this->addResult('status', 'error');
					$this->addResult('message', 'Token doesnt exists.');
					return $this->getResponse();
				}
			}
			else{
				$this->addResult('status', 'error');
				$this->addResult('message', 'User doesnt exists.');
				return $this->getResponse();
			}
		}

	}
