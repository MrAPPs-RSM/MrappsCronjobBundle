<?php

namespace Mrapps\CronjobBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/cronjob")
 */
class CronjobController extends Controller
{
    /**
     * @Route("/nextstep",name="cron_nextstep")
     */
    public function nextstepAction(Request $request)
    {
        $result = $this->container->get('smoll.handler.cronjob')->eseguiProssimaChiamata();
        
        $success = $result['success'];
        $output = $result['output'];
        
        /* @var $logChiamata \Mrapps\CronjobBundle\Entity\CronLogChiamata */
        $logChiamata = $result['log_chiamata'];
        
        $response = array(
            'success' => $success,
            'output' => $output,
        );
        
        if($logChiamata !== null) {
            
            $logChiamataId = $logChiamata->getId();
            $logGruppoId = ($logChiamata->getLogGruppo() !== null) ? $logChiamata->getLogGruppo()->getId() : null;
            
            $chiamata = $logChiamata->getChiamata();
            $confChiamataId = ($chiamata !== null) ? $chiamata->getId() : null;
            $confGruppoId = ($chiamata->getGruppo() !== null) ? $chiamata->getGruppo()->getId() : null;
            
            $response = array_merge($response, array(
                'log_chiamata' => $logChiamataId,
                'log_gruppo' => $logGruppoId,
                'conf_chiamata' => $confChiamataId,
                'conf_gruppo' => $confGruppoId,
            ));
        }
        
        return new JsonResponse($response);
    }
    
    /**
     * @Route("/test",name="cron_test")
     */
    public function testAction(Request $request) {
        $number = mt_rand(1, 99999);
        $success = ($number%2 == 1);
        $output = 'Numero estratto: '.$number;
        
        return new Response(new \Mrapps\CronjobBundle\Model\CronjobResponse($success, $output, array('numero' => $number)));
    }
}