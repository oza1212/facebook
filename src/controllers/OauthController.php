<?php
/**
 * @link      https://dukt.net/craft/facebook/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/facebook/docs/license
 */

namespace dukt\facebook\controllers;

use Craft;
use craft\web\Controller;

class OauthController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $oauthProvider = 'facebook';


    // Public Methods
    // =========================================================================

    /**
     * Connect
     *
     * @return null
     */
    public function actionConnect()
    {
        // referer

        $referer = Craft::$app->getSession()->get('facebook.referer');

        if (!$referer)
        {
            $referer = Craft::$app->request->referrer;

            Craft::$app->getSession()->set('facebook.referer', $referer);
        }

/*        FacebookPlugin::log('Connect - Step 1'."\r\n".print_r([
                'referer' => $referer,
            ], true), LogLevel::Info, true);*/


        // connect

        if ($response = \dukt\oauth\Plugin::getInstance()->oauth->connect(array(
            'plugin'   => 'facebook',
            'provider' => $this->oauthProvider,
            'scope'   => Craft::$app->config->get('oauthScope', 'facebook'),
            'authorizationOptions'   => Craft::$app->config->get('oauthAuthorizationOptions', 'facebook')
        )))
        {
            if($response && is_object($response) && !$response->data)
            {
                return $response;
            }

            if ($response['success'])
            {
                // token
                $token = $response['token'];

                // save token
                \dukt\facebook\Plugin::getInstance()->facebook_oauth->saveToken($token);

/*                FacebookPlugin::log('Connect - Step 2'."\r\n".print_r([
                        'token' => $token,
                    ], true), LogLevel::Info, true);*/

                // session notice
                Craft::$app->getSession()->setNotice(Craft::t('app', "Connected to Facebook."));
            }
            else
            {
                // session error
                Craft::$app->getSession()->setError(Craft::t('app', $response['errorMsg']));
            }
        }
        else
        {
            // session error
            Craft::$app->getSession()->setError(Craft::t('app', "Couldn’t connect"));
        }

        // OAuth Step 5

        // redirect

        Craft::$app->getSession()->remove('facebook.referer');

        return $this->redirect($referer);
    }

    /**
     * Disconnect
     *
     * @return null
     */
    public function actionDisconnect()
    {
        if (\dukt\facebook\Plugin::getInstance()->facebook_oauth->deleteToken())
        {
            Craft::$app->getSession()->setNotice(Craft::t('app', "Disconnected from Facebook."));
        }
        else
        {
            Craft::$app->getSession()->setError(Craft::t('app', "Couldn’t disconnect from Facebook"));
        }

        // redirect
        $redirect = Craft::$app->request->getUrlReferrer();
        return $this->redirect($redirect);
    }
}