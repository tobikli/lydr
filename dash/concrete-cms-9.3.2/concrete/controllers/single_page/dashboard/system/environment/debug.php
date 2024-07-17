<?php

namespace Concrete\Controller\SinglePage\Dashboard\System\Environment;

use Concrete\Core\Http\Request;
use Concrete\Core\Page\Controller\DashboardPageController;
use Exception;

class Debug extends DashboardPageController
{
    public function view()
    {
        $config = $this->app->make('config');
        $this->set('debug_enabled', (bool) $config->get('concrete.debug.display_errors'));
        $this->set('debug_detail', $config->get('concrete.debug.detail'));
        $this->set('warnings_as_errors', (string) $config->get('concrete.debug.error_reporting') !== '');
    }

    public function update_debug()
    {
        if ($this->token->validate('update_debug')) {
            if ($this->request->isMethod(Request::METHOD_POST)) {
                $post = $this->request->request;
                $config = $this->app->make('config');
                $config->save('concrete.debug.display_errors', (bool) $post->get('debug_enabled'));
                $config->save('concrete.debug.detail', $post->get('debug_detail'));
                $config->save('concrete.debug.error_reporting', $post->get('warnings_as_errors') ? -1 : null);

                $this->flash('success', t('Debug configuration saved.'));

                return $this->buildRedirect($this->action());
            }
        } else {
            $this->error->add($this->token->getErrorMessage());
        }
    }

    public function debug_example()
    {
        $config = $this->app->make('config');
        $config->set('concrete.log.errors', false);
        $config->set('concrete.debug.display_errors', true);
        $config->set('concrete.debug.detail', 'debug');

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'debug_example';

        error_reporting($this->request->query->get('warnings_as_errors') ? -1 : DEFAULT_ERROR_REPORTING);
        $this->produceNotice();

        throw new ExampleException('Sample Debug Output!');
    }

    public function message_example()
    {
        $config = $this->app->make('config');
        $config->set('concrete.log.errors', false);
        $config->set('concrete.debug.display_errors', true);
        $config->set('concrete.debug.detail', 'message');

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'debug_example';

        error_reporting($this->request->query->get('warnings_as_errors') ? -1 : DEFAULT_ERROR_REPORTING);
        $this->produceNotice();

        throw new ExampleException('Sample Message Output!');
    }

    public function disabled_example()
    {
        $config = $this->app->make('config');
        $config->set('concrete.log.errors', false);
        $config->set('concrete.debug.display_errors', false);

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'debug_example';

        error_reporting($this->request->query->get('warnings_as_errors') ? -1 : DEFAULT_ERROR_REPORTING);
        $this->produceNotice();

        throw new ExampleException('Sample Disabled Output!');
    }

    protected function produceNotice(): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->will_throw_a_notice_because = $this->is_not_a_defined_property;
        } else {
            unserialize('this will throw an E_NOTICE');
        }
    }
}

class ExampleException extends Exception
{
}
