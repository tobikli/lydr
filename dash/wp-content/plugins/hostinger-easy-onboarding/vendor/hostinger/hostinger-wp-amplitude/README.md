# WordPress Menu Manager PHP package

Package for managing Hostinger Amplitude events.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Code Testing](#code-testing)
- [Support](#support)

## Installation

This is private package, adding it to composer is a bit different.

Add it to the composer.json:
```sh
"repositories": [
{
"type": "vcs",
"url": "git@github.com:hostinger/hostinger-wp-amplitude.git"
}
],
```

and:
```sh
"require": {
"hostinger/hostinger-wp-amplitude": "dev-main", 
}
```

## Usage

First thing boot up package in main plugin file:

```sh
use Hostinger\Amplitude\Loader;

if( !function_exists('hostinger_load_amplitude') ) {
    function hostinger_load_amplitude(): void {
        $amplitude = AmplitudeLoader::getInstance();
        $amplitude->boot();
    }
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_amplitude' ) ) {
add_action('plugins_loaded', 'hostinger_load_amplitude');
}
```

You are ready to go. There are several hooks for use.

**getSingleAmplitudeEvents** - Add amplitude events that should trigger only once per day.

```sh
add_filter('hostinger_once_per_day_events', function($amplitudeEvents) {
$eventsList = [
    'wordpress.home.enter',
    'wordpress.learn.enter',
    'wordpress.ai_assistant.enter',
];

$amplitudeEvents = array_merge($amplitudeEvents, $eventsList)

return $amplitudeEvents;
});
```

**SendRequest** - Submit amplitude event from backend.

```sh
namespace Hostinger\Amplitude;

use Hostinger\Amplitude\AmplitudeManager;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;

$helper          = new Helper();
$configHandler   = new Config();
$client          = new Client(
    $configHandler->getConfigValue( 'base_rest_uri', Constants::HOSTINGER_REST_URI ),
    array(
        Config::TOKEN_HEADER  => $helper::getApiToken(),
        Config::DOMAIN_HEADER => $helper->getHostInfo()
    )
);

$params = [
    'action' => 'wp_admin.woocommerce_onboarding.setup_store',
    'location' => 'wordpress',
]

$amplitudeManger = new AmplitudeManager( $helper, $configHandler, $client );
$amplitudeManger->sendRequest( $amplitudeManger::AMPLITUDE_ENDPOINT, $params );
```

**SendRequest** - Submit amplitude event from frontend.

```sh
var data = {
    action: 'yourAction',
    location: 'yourLocation'
};

fetch('https://yourWebsiteUrl/wp-json/hostinger-amplitude/v1/hostinger-amplitude-event', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
    .then(response => response.json())
    .then(data => console.log(data))
    .catch((error) => {
      console.error('Error:', error);
});
```

## Support

Package initially was written by Martynas Umbraziunas (martynas.umbraziunas@hostinger.com). You can ping him in Slack for support.
