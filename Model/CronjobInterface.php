<?php

namespace Mrapps\CronjobBundle\Model;

use Symfony\Component\DependencyInjection\Container;

interface CronjobInterface
{
    /**
     * 
     * @param Container $container
     * @param array $parameters
     * @return mixed
     */
    public function run(Container $container, array $parameters);
}