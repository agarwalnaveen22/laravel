<?php 

namespace App;
use Auth;
use Illuminate\Contracts\Auth\Guard; 
use Socialite; 
use App\Repositories\UserRepository; use Request; 
use Illuminate\Support\Facades\Redirect;
use Session;

class AuthenticateUser {     
    private $socialite;
    private $auth;
    private $users;

    public function __construct(Socialite $socialite, Guard $auth, UserRepository $users) {
        $this->socialite = $socialite;
        $this->users = $users;
        $this->auth = $auth;
    }

    public function execute($request, $listener, $provider) {
        if (!$request) {
            return $this->getAuthorizationFirst($provider);
        }
        //var_dump($this->getSocialUser($provider));
        $user = $this->users->findByUserNameOrCreate($this->getSocialUser($provider));
        if(!empty($user)){
            Auth::login($user, true);
            return $listener->userHasLoggedIn($user);
        } else {
            Session::put('custom_error','We are unable to retrieve your email address from Facebook. Please update your privacy settings.');        
            return Redirect::to('/auth/register');
        }
    }

    private function getAuthorizationFirst($provider) {
        return Socialite::driver($provider)->redirect();
    }

    private function getSocialUser($provider) {
        return Socialite::driver($provider)->user();
    }
}
