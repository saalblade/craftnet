<?php

namespace craftnet\oauthserver\server\Repositories;

use craftnet\oauthserver\Module;
use craftnet\oauthserver\server\Entities\ClientEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     * @param bool $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        $client = Module::getInstance()->getClients()->getClientByIdentifier($clientIdentifier);

        if ($client) {
            $clientEntity = new ClientEntity;
            $clientEntity->setIdentifier($client->identifier);
            $clientEntity->setName($client->name);

            if ($client->redirectUriLocked) {
                $clientEntity->setRedirectUri($client->redirectUri);
            }

            return $clientEntity;
        }

        throw new \Exception("Client ID not found.");
    }
}
