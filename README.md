# Shipment Tracking Library For DHL eCommerce

[![Build Status](https://img.shields.io/travis/slince/shipment-tracking/master.svg?style=flat-square)](https://travis-ci.org/slince/shipment-tracking)
[![Coverage Status](https://img.shields.io/codecov/c/github/slince/shipment-tracking.svg?style=flat-square)](https://codecov.io/github/slince/shipment-tracking)
[![Latest Stable Version](https://img.shields.io/packagist/v/slince/shipment-tracking.svg?style=flat-square&label=stable)](https://packagist.org/packages/slince/shipment-tracking)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/slince/shipment-tracking.svg?style=flat-square)](https://scrutinizer-ci.com/g/slince/shipment-tracking/?branch=master)

A flexible and shipment tracking library for DHL eCommerce or DHL eCommerce(Registered)

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
}

```
The above code will get access token automatically for shipment information.

### Access Token


```php
$accessToken = $tracker->getAccessToken(); //You can save this for the next query

//... to do

try{
    $tracker->setAccessToken($accessToken); //Set the access token; the tracker will not send requst for the access token
    $shipment = $tacker->track('CNAQV100168101);
} catch () {
}
```
## License
 
The MIT license. See [MIT](https://opensource.org/licenses/MIT)

