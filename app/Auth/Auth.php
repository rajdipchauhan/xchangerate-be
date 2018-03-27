<?php

namespace App\Auth;

use App\Models\User;
use App\Models\WhitelistedToken;
use Firebase\JWT\JWT;

class Auth
{
    protected $jwt;
    protected $user;
    protected $key;
    protected $allowed_algs;

    public function __construct()
    {
        $this->key = env('JWT_SECRET', '4Pe6nPWXE5kNiyUyAaUirDn0MdQCNfBx');
        $this->allowed_algs = ['HS256'];
    }

    public function check()
    {
        return $this->user ? true : false;
    }

    public function user()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getJwt()
    {
        return $this->jwt;
    }

    /**
     * @param mixed $jwt
     * @return bool
     */
    public function setJwt($jwt)
    {
        $this->jwt = $jwt;
        $decoded = $this->validateAndDecodeJwt($jwt);
        if (! $decoded) {
            return false;
        }
        $this->user = User::find($decoded->sub);

        return $this->check();
    }

    protected function validateAndDecodeJwt($jwt)
    {
        $whitelistedJwt = WhitelistedToken::byToken($jwt)->first();
        if (! $whitelistedJwt) {
            return false;
        }
        $decoded = JWT::decode($jwt, $this->key, $this->allowed_algs);
        if ($decoded->exp && $decoded->exp < time()) {
            return false;
        }
        if ($decoded->sub != $whitelistedJwt->user_id) {
            return false;
        }

        return $decoded;
    }

    public function generateJwt(User $user, $remember = false)
    {
        $expiryDate = ! $remember ? strtotime('+1 month') : null;

        $token = [
            'sub' => $user->id,
            'iss' => null,
            'exp' => $expiryDate,
            'aud' => null,
            'iat' => time(),
            'nbf' => time(),
            'jti' => null
        ];
        $jwt = JWT::encode($token, $this->key);

        WhitelistedToken::create([
            'user_id' => $user->id, 
            'token' => $jwt
        ]);

        return $jwt;
    }

    public function invalidateJwt($jwt = null)
    {
        if (! $jwt) {
            $jwt = $this->jwt;
        }
        WhitelistedToken::byToken($jwt)->delete();
    }
}