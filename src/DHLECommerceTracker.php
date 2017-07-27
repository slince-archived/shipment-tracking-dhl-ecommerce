<?php
/**
 * Slince shipment tracker library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\ShipmentTracking\DHLECommerce;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Slince\ShipmentTracking\Shipment;
use Slince\ShipmentTracking\ShipmentEvent;
use Slince\ShipmentTracking\HttpAwareTracker;
use Slince\ShipmentTracking\Exception\RuntimeException;
use Slince\ShipmentTracking\Exception\TrackException;

class DHLECommerceTracker extends HttpAwareTracker
{
    /**
     * @var string
     */
    const ACCESS_TOKEN_ENDPOINT = 'https://api.dhlecommerce.dhl.com/rest/v1/OAuth/AccessToken';

    /**
     * @var string
     */
    const TRACKING_ENDPOINT = 'https://api.dhlecommerce.dhl.com/rest/v2/Tracking';

    /**
     * @var Credential
     */
    protected $credential;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    public function __construct($clientId, $password, HttpClient $httpClient = null)
    {
        $this->setHttpClient($httpClient);
        $this->credential = new Credential($clientId, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function track($trackingNumber)
    {
        $accessToken = $this->accessToken ?: $this->getAccessToken();
        $parameters = [
            'trackItemRequest' => [
                'token' => $accessToken,
                'messageLanguage' => 'en',
                'messageVersion' => '1.1',
                'trackingReferenceNumber' => [$trackingNumber]
            ]
        ];
        try {
            $response = $this->getHttpClient()->post(static::TRACKING_ENDPOINT, [
                'json' => $parameters
            ]);
            $json = \GuzzleHttp\json_decode((string)$response->getBody(), true);
            if ($json['trackItemResponse']['responseCode'] != 0) {
                throw new RuntimeException(sprintf('Bad response with code "%d"', $json['code']));
            }
            return static::buildShipment($json);
        } catch (GuzzleException $exception) {
            throw new TrackException($exception->getMessage());
        }
    }

    /**
     * Sets the access token for the tracker
     * @param string|AccessToken $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken instanceof AccessToken ? $accessToken : new AccessToken($accessToken);
    }

    /**
     * Gets the access token
     * @param boolean $refresh
     * @throws RuntimeException
     * @return AccessToken
     */
    public function getAccessToken($refresh = false)
    {
        if ($this->accessToken && !$refresh) {
            return $this->accessToken;
        }
        $response = $this->getHttpClient()->get(static::ACCESS_TOKEN_ENDPOINT, [
            'query' => array_merge($this->credential->toArray(), [
                'returnFormat' => 'json'
            ])
        ]);
        $json = \GuzzleHttp\json_decode((string)$response->getBody(), true);
        if (!isset($json['accessTokenResponse']) || $json['accessTokenResponse']['responseStatus']['code'] != '100000') {
            throw new RuntimeException(sprintf('Error to get access token'));
        }
        return $this->accessToken = new AccessToken(
            $json['accessTokenResponse']['token'],
            $json['accessTokenResponse']['token_type'],
            $json['accessTokenResponse']['expires_in_seconds']
        );
    }

    /**
     * @return Credential
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (!is_null($this->httpClient)) {
            return $this->httpClient;
        }
        return $this->httpClient = new HttpClient();
    }

    /**
     * @param array $json
     * @throws TrackException
     * @return Shipment
     */
    protected static function buildShipment($json)
    {
        $json = reset($json['trackItemResponse']['items']);
        if (!$json) {
            throw new TrackException(sprintf('Bad response'));
        }
        $events = array_map(function($item) {
            return ShipmentEvent::fromArray([
                'location' => $item['address']['city'],
                'description' => $item['description'],
                'date' => $item['timestamp'],
                'status' => $item['status']
            ]);
        }, $json['events']);
        $shipment = new Shipment($events);
        $isDelivered = ($lastEvent = end($events)) ? $lastEvent->getStatus() == 71093 : null;
        $shipment->setIsDelivered($isDelivered)
            ->setDestination($json['destination']['countryCode']);
        if ($firstEvent = reset($events)) {
            $shipment->setDeliveredAt($firstEvent->getDate());
        }
        return $shipment;
    }
}