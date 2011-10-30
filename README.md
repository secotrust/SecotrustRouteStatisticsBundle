# RouteStatisticsBundle #

## About ##

This bundle scans your application or web server log files and displays an
ordered overview of the matched routes. You can use this output to optimize
the order of the routes to speed up the matching.

## Installation ##

Append the following lines to your `deps` file:

    [SecotrustRouteStatisticsBundle]
        git=http://github.com/secotrust/SecotrustRouteStatisticsBundle.git
        target=/bundles/Secotrust/Bundle/RouteStatisticsBundle

then run the `php bin/vendors install` command.

Register the `Secotrust` namespace in the autoloader:

``` php
<?php
// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'Secotrust'        => __DIR__.'/../vendor/bundles',
    // ...
));
```

Register the bundle in your application's kernel:

``` php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Secotrust\Bundle\RouteStatisticsBundle\SecotrustRouteStatisticsBundle(),
        // ...
    );
    ...
}
```

## Usage ##

The bundle adds the `router:statistics` command. You can specify the `--format`
argument (default: `profiler`) which can be one of `apache`, `nginx`, `profiler`,
`symfony` or a regular expression which extracts the method and the path info from
a log line. When you are not using the `profiler` or `symfony` format then you can
optionally specify a path info prefix, for example `/app_dev.php` which will
be removed from the extracted path info.

The output contains the following informations:

* Match count
* Route name
* Position of the route in the matcher class

To optimize the routing you should try to move the most used routes to the top.
Remember that the first matching route always wins.

## Examples ##

    php app/console router:statistics

```
21: _wdt (#11)
 5: _demo (#8)
 4: _configurator_step (#20)
 3: _demo_secured_hello (#6)
 3: _welcome (#1)
 3: _demo_login (#2)
 2: _demo_secured_hello_admin (#7)
 2: _demo_hello (#9)
 1: _configurator_final (#21)
 1: _configurator_home (#19)
```

When using the `symfony` format the command looks for lines containing
`request.INFO: Matched route`.

    php app/console router:statistics --format symfony app/logs/dev.log

If you want to analyze your apache or nginx log files (`combined` log type)
your command should look like this:

    php app/console router:statistics --format apache /var/log/apache2/access-prod.log

Or if your log contains the front controller script name:

    php app/console router:statistics --format apache --prefix "/app_dev.php" /var/log/apache2/access-dev.log

## Known issues ##

* Currently the output differs a bit, symfony does not always log the matched route in the app log.
* When using web server logs, the scheme is unknown.
