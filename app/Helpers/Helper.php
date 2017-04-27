<?php

namespace App\Helpers;

use App\User;
use Hash;
use Response as output;
use Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Validator;
use App\Helpers\Jwt;
use App\Helpers\Response as Response;
/*
 * @author eddymens <eddymens@devless.io>
*composed of most common used classes and functions
*/

class Helper
{
    use messageStack;
    /**
     * fetch message based on status code.
     *
     * @param stack $stack
     *
     * @return string or null
     **/
    public static function responseMessage($stack)
    {
        if (isset(self::$MESSAGE_HEAP[$stack])) {
            return self::$MESSAGE_HEAP[$stack];
        } else {
            return;
        }
    }

    /**
     *  Abort execution and show message.
     *
     * @param error code      $stack
     * @param output message  $message
     * @param additional data $payload
     *
     * @return Exception
     */
    public static function interrupt($stack, $message = null, $payload = [])
    {
        $message = ($message !== null) ? $message : self::responseMessage($stack);
        throw new HttpException(500, $message, null, $payload, $stack);
    }

    /**
     * check the validility of a field type
     * uses laravel validator.
     *
     * @param string                             $field_value
     * @param string parameters to check against $check_against
     *
     * @return bool
     */
    public static function field_check($field_value, $check_against)
    {
        //convert check against to field_name for err_msg
        $field_name = $check_against;

        //check if multiple rules are used
        if (strpos($check_against, '|')) {
            $rules = explode('|', $check_against);

            foreach ($rules as $rule) {
                //convert each rule and re-combine
                if (!isset(self::$validator_type[$rule])) {
                    self::interrupt(618, 'validator type '.$rule.
                            ' does not exist');
                }
                $check_against = self::$validator_type[$rule].'|';
            }
        } else {
            //single validator rule convert field type to lowercase
            $check_against = strtolower($check_against);

            if (!isset(self::$validator_type[$check_against])) {
                self::interrupt(618, 'validator type '.$check_against.
                            ' does not exist');
            }
            $check_against = self::$validator_type[$check_against];
        }


        $state = Validator::make(
            [$field_name => $field_value],
            [$field_name => $check_against]
        );
        if ($state->fails()) {
            return false;
        }

        return true;
    }

    /**
     * get url parameters.
     *
     * @return array
     **/
    public static function query_string()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            $originalQueryString = $_SERVER['QUERY_STRING'];

            $query = explode('&', $originalQueryString);
            $params = [];
            foreach ($query as $param) {
                if ($param !== '') {
                    list($name, $value) = explode('=', $param, 2);
                    $params[urldecode($name)][] = urldecode($value);
                }
            }

            return $params;
        } else {
            return '';
        }
    }

    /**
     * Hash password.
     *
     * @param type $password
     *
     * @return string
     *
     * @internal param type $hash
     * @internal param array $rules
     */
    public static function password_hash($password)
    {
        return Hash::make($password);
    }

    /**
     * compare password hash.
     *
     * @param string $user_input
     * @param string $hash
     *
     * @return bool
     */
    public static function compare_hash($user_input, $hash)
    {
        return Hash::check($user_input, $hash);
    }

    /**
     * Check if current user is admin.
     *
     * @return mixed
     */
    public static function is_admin_login()
    {
        return (Session()->has('user')) ? true : false;
    }

    /**
     * Get authenticated user cred.
     * @param $access_state
     * @return array
     */
    public static function get_authenticated_user_cred($access_state)
    {
        $user_token = request()->header('devless-user-token');
        $user_cred = [];
        if (self::is_admin_login() && $access_state == true) {
            $admin = User::where('role', 1)->first();
            $user_cred['id'] = $admin->id;
            $user_cred['token'] = 'non for admin';
        } elseif ($user_token !== null && $access_state == true) {
            $user_data = self::verify_user_token($user_token);

            if (isset($user_data->id)) {
                $user_cred =
                    [
                        'id'    => $user_data->id,
                        'token' => $user_data->session_token,

                    ];
            } else {
                self::interrupt(628, null, [], true);
            }
        } elseif (!$access_state == false) {
            self::interrupt(628, null, [], true);
        }

        return $user_cred;
    }

    /**
     * Verify user token.
     *
     * @param $user_token
     *
     * @return mixed
     */
    public static function verify_user_token($user_token)
    {
        $secret = config('app')['key'];

        $jwt = new jwt();

        $jwt_payload = json_decode($jwt->decode($user_token, $secret, true));

        if ($user_token == 'null') {
            Self::interrupt(633, null, [], true);
        }
        $user_data = User::where('session_token', $jwt_payload->token)
               ->first();
        if ($user_data !== null) {
            $d1 = new \DateTime($user_data->session_time);
            $d2 = new \DateTime();
            $interval = $d1->diff($d2);

            if ($interval->h >= 1 || $interval->days > 0) {
                $user_data->session_token = '';
                $user_data->save();
                Self::interrupt(633, null, [], true);
            }

            $user_data->session_time = self::session_timestamp();
            $user_data->save();
        }



        return $user_data;
    }

    /**
     * Generate session timestamp.
     *
     * @return bool|string
     */
    public static function session_timestamp()
    {
        return date('Y-m-d H:i:s');
    }
}
