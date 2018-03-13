<?php
namespace Illuminate\View\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\ViewErrorBag;

class ShareErrorsFromSession
{
    /**
     * The view factory implementation.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param  \Illuminate\Contracts\View\Factory $view
     * @return void
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app = $this->view->getEnvironment()->getContainer();

        // If the current session has an "errors" variable bound to it, we will share
        // its value with all view instances so the views can easily access errors
        // without having to bind. An empty bag is set when there aren't errors.
        if ($this->sessionHasErrors($app)) {
            $errors = $app['session.store']->get('errors');

            $app['view']->share('errors', $errors);
        }

        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.
        else {
            $this->view->share('errors', new ViewErrorBag);
        }

        return $next($request);
    }

    /**
     * Determine if the application session has errors.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return bool
     */
    public function sessionHasErrors($app)
    {
        $config = $app['config']['session'];

        if (isset($app['session.store']) && !is_null($config['driver'])) {
            return $app['session.store']->has('errors');
        }
    }

}
