<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FacebookController extends Controller
{
	protected $clientRegistry;

	/**
	 * Use the constructor to autowire your dependencies
	 *
	 * @param ClientRegistry $clientRegistry
	 */
	public function __construct(
        ClientRegistry $clientRegistry
    ) {
        // add your dependencies
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/facebook", name="connect_facebook_start")
     */
    public function connectAction()
    {
        // will redirect to Facebook!
        return $this->clientRegistry
            ->getClient('facebook_main') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect(
                ['public_profile', 'email'] // the scopes you want to access
            )
        ;
	}

    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     */
    public function connectCheckAction(Request $request)
    {
        // ** if you want to *authenticate* the user, then
		// leave this method blank and create a Guard authenticator

		return new JsonResponse($this->getUser()->getFacebookId());
    }
}
