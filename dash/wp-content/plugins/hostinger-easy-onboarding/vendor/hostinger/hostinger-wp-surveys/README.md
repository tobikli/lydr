# WordPress Hostinger Surveys PHP package

Package for managing Hostinger WordPress surveys.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Support](#support)

## Installation

This is private package, adding it to composer is a bit different.

Add it to the composer.json:
```sh
"repositories": [
{
"type": "vcs",
"url": "git@github.com:hostinger/hostinger-wp-surveys.git"
}
],
```

and:
```sh
"require": {
"hostinger/hostinger-surveys": "main", 
}
```

## Usage

First thing boot up package in main plugin file:

```sh
use Hostinger\Surveys\Loader;
if( !function_exists('hostinger_load_surveys') ) {
function hostinger_load_surveys(): void {
$surveys = Loader::get_instance();
$surveys->boot();
}
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_surveys' ) ) {
add_action('plugins_loaded', 'hostinger_load_surveys');
}

```

Then boot Surveys class with all surveys logic

```sh
use Hostinger\Surveys\SurveyManager;
if ( class_exists( SurveyManager::class ) ) {
$surveys = new Surveys();
$surveys->init();
}

```

And here is example of Surveys class

```sh
<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\Surveys\SurveyManager;

class Surveys {
const CHATBOT_SURVEY_NAME = 'chatbot';
const CHATBOT_SURVEY_SCORE_QUESTION = 'How would you rate our AI chatbot? (1-10)';
const CHATBOT_SURVEY_COMMENT_QUESTION = 'How would you rate your experience with the Chatbot? (Comment)';
const CHATBOT_SURVEY_LOCATION = 'wp-admin';
const SURVEY_PRIORITY = 999;

public function init() {
  add_filter( 'hostinger_add_surveys', [ $this, 'createSurveys' ] );
}

public function createSurveys( $surveys ) {
  $scoreQuestion   = esc_html__( self::CHATBOT_SURVEY_SCORE_QUESTION, 'hostinger-easy-onboarding' );
  $commentQuestion = esc_html__( self::CHATBOT_SURVEY_COMMENT_QUESTION, 'hostinger-easy-onboarding' );
  $chatbotSurvey   = SurveyManager::addSurvey( self::CHATBOT_SURVEY_NAME, $scoreQuestion, $commentQuestion, self::CHATBOT_SURVEY_LOCATION, self::SURVEY_PRIORITY );
  $surveys[] = $chatbotSurvey;

  return $surveys;
}
}

```

## Support

Package initially was written by Martynas U. (martynas.umbraziunas@hostinger.com). You can ping him in Slack for support.
