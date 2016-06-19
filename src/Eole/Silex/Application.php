<?php

namespace Eole\Silex;

use Eole\Sandstone\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@InheritDoc}
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->logErrors();
        $this->checkConstants();
        $this->loadEnvironmentParameters();
        $this->registerSilexProviders();
        $this->registerSecurity();
        $this->registerServices();
        $this->registerListeners();
        $this->loadAllMods();
        $this->registerDoctrine();
        $this->registerOAuth2Security();

        if ($this['debug']) {
            $this->enableProfiler();
        }
    }

    /**
     * Check whether application constants are well defined.
     */
    private function checkConstants()
    {
        if (!isset($this['project.root'])) {
            throw new \LogicException('project.root must be defined.');
        }

        if (!isset($this['env'])) {
            throw new \LogicException('env must be defined.');
        }

        $environments = array('dev', 'test', 'prod');

        if (!in_array($this['env'], $environments)) {
            throw new \DomainException('env must be one of: "'.implode('", "', $environments).'".');
        }
    }

    /**
     * Load config/environment.yml or config/environment.yml.dist.
     *
     * @throws Exception if file not found.
     */
    private function loadEnvironmentParameters()
    {
        $parser = new \Symfony\Component\Yaml\Parser();
        $environmentFile = $this['project.root'].'/config/environment.yml';
        $extEnvironmentFile = $this['project.root'].'/config/environment_'.$this['env'].'.yml';

        if (!file_exists($environmentFile)) {
            throw new \LogicException($environmentFile.' not found, unable to load environment parameters.');
        }

        $environment = $parser->parse(file_get_contents($environmentFile));

        if (file_exists($extEnvironmentFile)) {
            $extEnvironment = $parser->parse(file_get_contents($extEnvironmentFile));
            $environment = array_replace_recursive($environment, $extEnvironment);
        }

        $this['environment'] = $environment;
    }

    /**
     * Register default silex providers
     */
    private function registerSilexProviders()
    {
        $this->register(new \Silex\Provider\ServiceControllerServiceProvider());
        $this->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.name' => 'eole',
            'monolog.logfile' => $this['project.root'].'/var/logs/monolog_'.$this['env'].'.log',
        ));
    }

    /*
     * Register Symfony security
     */
    private function registerSecurity()
    {
        $userProvider = function () {
            return new \Alcalyn\UserApi\Security\UserProvider($this['eole.player_api']);
        };

        $this->register(new \Silex\Provider\SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'api' => array(
                    'pattern' => '^/api',
                    'oauth' => true,
                    'stateless' => true,
                    'anonymous' => true,
                    'users' => $userProvider,
                ),
            ),
        ));

        $this['security.default_encoder'] = function () {
            return $this['security.encoder.digest'];
        };

        $this['eole.user_provider'] = $userProvider;
    }

    /*
     * Register OAuth2 Security
     */
    private function registerOAuth2Security()
    {
        $this->register(new \Eole\Sandstone\OAuth2\Silex\OAuth2ServiceProvider(), array(
            'oauth.firewall_name' => 'api',
            'oauth.clients' => $this['environment']['oauth']['clients'],
            'oauth.tokens_dir' => $this['project.root'].'/var/oauth-tokens',
            'oauth.security.user_provider' => $this['eole.user_provider'],
        ));
    }

    /**
     * Register doctrine DBAL and ORM
     */
    private function registerDoctrine()
    {
        $this->registerDoctrineDBAL();
        $this->registerDoctrineORM();
    }

    /**
     * Register doctrine DBAL
     */
    private function registerDoctrineDBAL()
    {
        $this->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => $this['environment']['database']['connection'],
        ));
    }

    /**
     * Register and configure doctrine ORM
     */
    private function registerDoctrineORM()
    {
        $this->register(new \Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), array(
            'orm.proxies_dir' => $this['project.root'].'/var/cache/doctrine/proxies',
            'orm.auto_generate_proxies' => $this['environment']['database']['orm']['auto_generate_proxies'],
            'orm.em.options' => array(
                'mappings' => $this['eole.mappings'],
            ),
        ));
    }

    /**
     * Enable profiler interface for debugging.
     *
     * @throws \LogicException If composer dev dependencies are not loaded.
     */
    private function enableProfiler()
    {
        if (!class_exists('\Silex\Provider\HttpFragmentServiceProvider', true)) {
            throw new \LogicException('You must load composer dev dependencies in order to use profiler.');
        }

        $this->register(new \Silex\Provider\HttpFragmentServiceProvider());
        $this->register(new \Silex\Provider\TwigServiceProvider());

        $this->register(new \Silex\Provider\WebProfilerServiceProvider(), array(
            'profiler.cache_dir' => $this['project.root'].'/var/cache/profiler',
            'profiler.mount_prefix' => '/_profiler',
        ));

        $this->register(new \Sorien\Provider\DoctrineProfilerServiceProvider());
    }

    /**
     * Register Eole services
     */
    private function registerServices()
    {
        $this->register(new \Eole\Sandstone\Serializer\ServiceProvider());

        $this['serializer.builder']->setCacheDir($this['project.root'].'/var/cache/serializer');

        $this->register(new \Eole\Sandstone\Websocket\ServiceProvider(), [
            'sandstone.websocket.server' => [
                'bind' => $this['environment']['websocket']['server']['bind'],
                'port' => $this['environment']['websocket']['server']['port'],
            ],
        ]);

        $this->register(new \Eole\Sandstone\PushServer\ServiceProvider(), [
            'sandstone.push.enabled' => true,
            'sandstone.push.server' => [
                'bind' => $this['environment']['push']['server']['bind'],
                'host' => $this['environment']['push']['server']['host'],
                'port' => $this['environment']['push']['server']['port'],
            ],
        ]);

        $this['eole.mappings'] = function () {
            $mappings = array();

            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Alcalyn\UserApi\Model',
                'path' => $this['project.root'].'/vendor/alcalyn/doctrine-user-api/Mapping',
            );

            return $mappings;
        };

        $this['eole.listener.authorization_header_fix'] = function () {
            return new \Alcalyn\AuthorizationHeaderFix\AuthorizationHeaderFixListener();
        };
    }

    /**
     * Register events listeners.
     */
    private function registerListeners()
    {
        $this->on(
            \Symfony\Component\HttpKernel\KernelEvents::REQUEST,
            array(
                $this['eole.listener.authorization_header_fix'],
                'onKernelRequest'
            ),
            10
        );
    }

    /**
     * Log errors.
     */
    private function logErrors()
    {
        $this->error(function (\Exception $e) {
            $logFile = $this['project.root'].'/var/logs/errors.txt';
            $message = get_class($e).' '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
        });
    }

    /**
     * @param string $modName
     *
     * @return Mod
     */
    public function instanciateMod($modName)
    {
        $modClass = $this['environment']['mods'][$modName]['provider'];

        $mod = new $modClass();

        if (!$mod instanceof Mod) {
            throw new \LogicException(sprintf(
                'Mod class (%s) for mod %s must implement %s.',
                get_class($mod),
                $modName,
                Mod::class
            ));
        }

        return $mod;
    }

    /**
     * @param string $modName
     *
     * @return Mod
     */
    public function loadMod($modName)
    {
        $mod = $this->instanciateMod($modName);
        $serviceProvider = $mod->createServiceProvider();

        if (null !== $serviceProvider) {
            $this->register($serviceProvider);
        }

        if (!$serviceProvider instanceof \Pimple\ServiceProviderInterface) {
            throw new \LogicException(sprintf(
                'Service provider class (%s) for mod %s must implement %s.',
                get_class($serviceProvider),
                $modName,
                \Pimple\ServiceProviderInterface::class
            ));
        }

        return $mod;
    }

    /**
     * Load all mods.
     *
     * @return self
     */
    public function loadAllMods()
    {
        $mods = $this['environment']['mods'];

        foreach ($mods as $modName => $config) {
            $this->loadMod($modName);
        }

        return $this;
    }

    /**
     * Returns all mods which implement GameProvider.
     *
     * @return GameProvider[]
     */
    public function getGameProviders()
    {
        $mods = $this['environment']['mods'];
        $gameProviders = array();

        foreach ($mods as $modName => $modClass) {
            $mod = $this->instanciateMod($modName);

            if ($mod instanceof GameProvider) {
                $gameProviders[$modName] = $mod;
            }
        }

        return $gameProviders;
    }
}
