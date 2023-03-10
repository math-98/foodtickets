<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('bugsnag', [
        'api_key' => '%env(BUGSNAG_API_KEY)%',
        'app_version' => date('Y-m-d H:i:s'),
        'discard_classes' => [
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        ],
    ]);
};
