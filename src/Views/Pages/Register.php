<?php
namespace Framework\Views\Pages;

/**
 * Lewis Lancaster 2016
 *
 * Class Register
 *
 * @package Framework\Views\Pages
 */

use Framework\Application\Settings;
use Framework\Application\Utilities\PostHelper;
use Framework\Views\Structures\Page;
use Framework\Application\Container;
use Framework\Application\Session;
use Framework\Syscrack\Register as Account;
use Flight;

class Register implements Page
{

    /**
     * Login constructor.
     */

    public function __construct()
    {

        if( session_status() !== PHP_SESSION_ACTIVE )
        {

            session_start();
        }

        Container::setObject( 'session',  new Session() );

        if( Container::getObject('session')->isLoggedIn() )
        {

            Flight::redirect( '/'. Settings::getSetting('controller_index_page') );
        }
    }

    /**
     * The index page has a special algorithm which allows it to access the root. Only the index can do this.
     *
     * @return array
     */

    public function mapping()
    {

        return array(
            [
                'GET /register/', 'page'
            ],
            [
                'POST /register/', 'process'
            ]
        );
    }

    /**
     * Default page
     */

    public function page()
    {

        Flight::render('syscrack/page.register');
    }

    /**
     * Processes the register request
     */

    public function process()
    {

        if( PostHelper::hasPostData() == false )
        {

            $this->redirectError('Blank Form');
        }

        if( Settings::getSetting('user_allow_registrations') == false )
        {

            $this->redirectError('Registration is currently disabled, sorry...');
        }

        if( PostHelper::checkForRequirements(['username','password','email']) == false )
        {

            $this->redirectError('Missing Information');
        }

        $username = PostHelper::getPostData('username'); $password = PostHelper::getPostData('password'); $email = PostHelper::getPostData('email');

        if( empty( $username ) || empty( $password ) || empty( $email ) )
        {

            $this->redirectError('Failed to register');
        }

        $register = new Account();

        if( strlen( $password ) < Settings::getSetting('registration_password_length') )
        {

            $this->redirectError('Your password is too small, it needs to be longer than ' . Settings::getSetting('registration_password_length') . ' characters');
        }

        try
        {

            $result = $register->register( $username, $password, $email );
        }
        catch( \Exception $error )
        {

            $this->redirectError( $error->getMessage() );
        }

        Flight::redirect('/verify/?token=' . $result );
    }

    /**
     * Display an error
     *
     * @param $error
     */

    private function redirectError( $error )
    {

        Flight::redirect('/register/?error=' . $error );
    }
}