<?php

namespace Mrapps\CronjobBundle\Cronjob;

use Mrapps\CronjobBundle\Model\CronjobInterface;
use Symfony\Component\DependencyInjection\Container;
use Mrapps\CronjobBundle\Model\CronjobResponse;

class TestCronjob implements CronjobInterface {
    
    public function run(Container $container, array $parameters) {
        
        $number = mt_rand(1, 99999);
        $success = ($number%2 == 0);
        $output = 'Numero estratto: '.$number;
        
        return new CronjobResponse($success, $output, array('numero' => $number));
    }
}
