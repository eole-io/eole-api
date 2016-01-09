<?php

namespace Eole\Silex;

use Symfony\Component\HttpFoundation\JsonResponse;
use Alcalyn\Wsse\Security\Authentication\Provider\PasswordDigestValidator;
use Alcalyn\SilexWsse\Provider\WsseServiceProvider;
use Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@InheritDoc}
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->handleProdErrors();
        $this->checkConstants();
        $this->loadEnvironmentParameters();
        $this->registerSilexProviders();
        $this->registerSecurity();
        $this->registerWsseSecurity();
        $this->registerServices();
        $this->loadAllGames();
        $this->registerDoctrine();
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
            $environment = array_merge($environment, $extEnvironment);
        }

        $this['environment'] = $environment;
    }

    /**
     * Register default silex providers
     */
    private function registerSilexProviders()
    {
        $this->register(new \Silex\Provider\ServiceControllerServiceProvider());
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
                    'wsse' => true,
                    'stateless' => true,
                    'anonymous' => true,
                    'users' => $userProvider,
                ),
            ),
        ));

        $this['security.encoder.digest'] = function () {
            return new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 42);
        };

        $this['eole.player_api'] = function () {
            return new \Eole\Core\Service\PlayerApi(
                $this['eole.player_manager'],
                $this['orm.em']->getRepository('Eole:Player')
            );
        };

        $this['eole.user_provider'] = $userProvider;
    }

    /*
     * Register SilexWsse Security
     */
    private function registerWsseSecurity()
    {
        $this['security.wsse.token_validator'] = function () {
            $wsseCacheDir = $this['project.root'].'/var/cache/wsse-tokens';
            return new PasswordDigestValidator($wsseCacheDir);
        };

        $this->register(new WsseServiceProvider('api'));
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
            'orm.em.options' => array(
                'mappings' => $this['eole.mappings'],
            ),
        ));
    }

    /**
     * Register Eole services
     */
    private function registerServices()
    {
        $this['serializer.context_factory'] = $this->protect(function () {
            return \JMS\Serializer\SerializationContext::create()
                ->setSerializeNull(true)
            ;
        });

        $this['serializer.builder'] = function () {
            return
                \JMS\Serializer\SerializerBuilder::create()
                ->addMetadataDir($this['project.root'].'/src/Eole/Core/Serializer')
                ->setCacheDir($this['project.root'].'/var/cache/serializer')
                ->setDebug($this['debug'])
            ;
        };

        $this['serializer'] = function () {
            return $this['serializer.builder']->build();
        };

        $this['eole.mappings'] = function () {
            $mappings = array();

            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Alcalyn\UserApi\Model',
                'path' => $this['project.root'].'/vendor/alcalyn/doctrine-user-api/Mapping',
            );

            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Eole\Core\Model',
                'path' => $this['project.root'].'/src/Eole/Core/Mapping',
                'alias' => 'Eole',
            );

            return $mappings;
        };

        $this['eole.player_manager'] = function () {
            $encoderFactory = $this['security.encoder_factory'];
            $userClass = \Eole\Core\Model\Player::class;

            return new \Eole\Core\Service\PlayerManager(
                $encoderFactory,
                $userClass
            );
        };

        $this['eole.party_manager'] = function () {
            return new \Eole\Core\Service\PartyManager();
        };

        $this['eole.event_serializer'] = function () {
            return new Service\EventSerializer($this['serializer']);
        };
    }

    /**
     * Display json errors in prod environment.
     */
    private function handleProdErrors()
    {
        $this->error(function (\Exception $e) {
            $logFile = $this['project.root'].'/var/logs/errors.txt';
            $message = get_class($e).' '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);

            if (true === $this['debug']) {
                return;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return new JsonResponse(array(
                    'statusCode' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                ));
            } else {
                return new JsonResponse(array(
                    'statusCode' => 500,
                    'message' => 'Internal Server Error.',
                ));
            }
        });
    }

    /**
     * Register a game service provider.
     *
     * @param string $gameName
     *
     * @return self
     */
    private function registerGame($gameName)
    {
        $gameConfig = $this['environment']['games'][$gameName];

        if (isset($gameConfig['service_provider'])) {
            $serviceProviderClass = $gameConfig['service_provider'];
            $serviceProvider = new $serviceProviderClass();

            if (!$serviceProvider instanceof \Pimple\ServiceProviderInterface) {
                throw new \LogicException(sprintf(
                    'Game service provider class (%s) for game %s must implement %s.',
                    $serviceProviderClass,
                    $gameName,
                    'Pimple\\ServiceProviderInterface'
                ));
            }

            $this->register($serviceProvider);
        }

        return $this;
    }

    /**
     * @param string $gameName
     *
     * @return self
     */
    public function loadGame($gameName)
    {
        $this->registerGame($gameName);

        return $this;
    }

    /**
     * @return self
     */
    public function loadAllGames()
    {
        $games = $this['environment']['games'];

        foreach ($games as $gameName => $config) {
            $this->loadGame($gameName);
        }

        return $this;
    }
}
