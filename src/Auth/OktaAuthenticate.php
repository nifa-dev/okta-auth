<?php

namespace OktaAuth\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\Http\Client;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\UnauthorizedException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Cake\ORM\TableRegistry;


class OktaAuthenticate extends BaseAuthenticate
{

    public function authenticate(ServerRequest $request, Response $response)
    {

        $authHeaderSecret = base64_encode(Configure::read('Okta.clientId') .
                                                ":" . Configure::read('Okta.clientSecret'));

        $http = new Client();
        $response = $http->post(Configure::read('Okta.tokenUrl'), [
                'grant_type' => 'authorization_code',
                'code' => $request->getQuery('code'),
                'redirect_uri' => Configure::read('Okta.redirectUrl')
            ], [
                'headers' => [
                    'Authorization' => 'Basic: ' . $authHeaderSecret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'

                ]
            ]);

        $responseData = json_decode($response->body);
        //return $response->body;
        Log::write('debug', $responseData);

        if(property_exists($responseData, 'error')) {
            throw new BadRequestException($responseData->error);
        }

        $jwt = $responseData->access_token;

        $keys = json_decode(file_get_contents('https://'.Configure::read('Okta.domain').'/oauth2/'.Configure::read('Okta.authorizationServerId').'/v1/keys'));
        Log::write('debug', $keys);
        $keys = JWK::parseKeySet($keys);
        JWT::$leeway = Configure::read('Okta.leeway') ? Configure::read('Okta.leeway'): 0;
        $decoded = JWT::decode($jwt, $keys, ['RS256']);
        Log::write('debug', $decoded);

        //compare issuer with authorizationServer
        if($decoded->iss != 'https://'.Configure::read('Okta.domain').'/oauth2/'.Configure::read('Okta.authorizationServerId')) {
            throw new UnauthorizedException(__('Issuer Mismatch Error'));
        }

        //compare client id from token with config
        if($decoded->cid != Configure::read('Okta.clientId')) {
            throw new UnauthorizedException(__('Client ID Mismatch Error'));
        }

        //compare issued time is within now+300
        if($decoded->iat > (time()+300)) {
            throw new UnauthorizedException(__('Token was issued in the future'));
        }

        //compare expiration time has not passed (with leeway for clock error)
        if($decoded->exp < (time()-300)) {
            throw new UnauthorizedException(__('Token has expired'));
        }


        //at this point the token is valid, log in the user...


        //find local user if exists, or create local user if doesn't exist
        
        $users = TableRegistry::get(Configure::read('Okta.usersModel'));

        $idTokenDecoded = JWT::decode($responseData->id_token, $keys, ['RS256']);

        //Check to see if user exists by email
        $user = $users->findByEmail($idTokenDecoded->email)->first();
        if($user) {
            return $user;
        }

        return false;

    }
}

?>