<?php

	namespace App\Http\Middleware;

	use App\Http\Controllers\Controller;
	use Closure;
	use Illuminate\Contracts\Auth\Factory as Auth;

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
			if($request->user() !== NULL){
				return $next($request);
			}
			else{
				$this->addMessage('error','User doesnt exists.');
				return $this->getResponse();
			}
		}

	}
