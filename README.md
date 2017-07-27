# Shipment Tracking Library For DHL eCommerce

[![Build Status](https://img.shields.io/travis/slince/shipment-tracking-dhl-ecommerce/master.svg?style=flat-square)](https://travis-ci.org/slince/shipment-tracking-dhl-ecommerce)
[![Coverage Status](https://img.shields.io/codecov/c/github/slince/shipment-tracking-dhl-ecommerce.svg?style=flat-square)](https://codecov.io/github/slince/shipment-tracking-dhl-ecommerce)
[![Latest Stable Version](https://img.shields.io/packagist/v/slince/shipment-tracking-dhl-ecommerce.svg?style=flat-square&label=stable)](https://packagist.org/packages/slince/shipment-tracking-dhl-ecommerce)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/slince/shipment-tracking-dhl-ecommerce.svg?style=flat-square)](https://scrutinizer-ci.com/g/slince/shipment-tracking-dhl-ecommerce/?branch=master)

A flexible and shipment tracking library for DHL eCommerce and DHL eCommerce(Registered)

## Installation

Install via composer

```bash
$ composer require slince/shipment-tracking-dhl-ecommerce
```
## Basic Usage


```php

$tracker = new Slince\ShipmentTracking\DHLECommerce\DHLECommerceTracker(CLIENT_ID, PASSWORD);

try {
   $shipment = $tracker->track('CNAQV100168101');
   
   if ($shipment->isDelivered()) {
       echo "Delivered";
   }
   echo $shipment->getOrigin();
   echo $shipment->getDestination();
   print_r($shipment->getEvents());  //print the shipment events
   
} catch (Slince\ShipmentTracking\Exception\TrackException $exception) {
    exit('Track error: ' . $exception->getMessage());
}

```
The above code will get access token automatically for shipment information.

### Access Token

```php
$shipment = $tacker->track('CNAQV100168101);
$accessToken = $tracker->getAccessToken(); //You can save this for the next query

//... to do

try{
    $tracker->setAccessToken($accessToken); //Set the access token; the tracker will not send requst for the access token
    $shipment = $tacker->track('CNAQV100168101);
} catch (Slince\ShipmentTracking\DHLECommerce\Exception\InvalidAccessTokenException $exception) {
     $accessToken = $tracker->getAccessToken(true); // If the access token is invalid, refresh it.
     $shipment = $tacker->track('CNAQV100168101);
     //... to do
} catch (Slince\ShipmentTracking\Exception\TrackException $exception) {
    exit('Track error: ' . $exception->getMessage());
}
```
## License
 
The MIT license. See [MIT](https://opensource.org/licenses/MIT)

