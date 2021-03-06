<?php

namespace Modules\Iticke\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Iticke\Events\Handlers\RegisterItickeSidebar;

class ItickeServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterItickeSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('tickets', array_dot(trans('iticke::tickets')));
            $event->load('ticketcomments', array_dot(trans('iticke::ticketcomments')));
            // append translations


        });
    }

    public function boot()
    {
        $this->publishConfig('iticke', 'permissions');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerBindings()
    {
        $this->app->bind(
            'Modules\Iticke\Repositories\TicketRepository',
            function () {
                $repository = new \Modules\Iticke\Repositories\Eloquent\EloquentTicketRepository(new \Modules\Iticke\Entities\Ticket());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Iticke\Repositories\Cache\CacheTicketDecorator($repository);
            }
        );
        $this->app->bind(
            'Modules\Iticke\Repositories\TicketCommentRepository',
            function () {
                $repository = new \Modules\Iticke\Repositories\Eloquent\EloquentTicketCommentRepository(new \Modules\Iticke\Entities\TicketComment());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Iticke\Repositories\Cache\CacheTicketCommentDecorator($repository);
            }
        );
// add bindings


    }
}
