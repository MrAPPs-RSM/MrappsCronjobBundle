<?php

namespace Mrapps\CronjobBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/mrapps_cronjob")
 */
class CronjobController extends Controller
{
    /**
     * @Route("/nextstep",name="mrapps_cronjob_nextstep")
     */
    public function nextstepAction(Request $request)
    {
        $result = $this->container->get('mrapps.cronjob')->eseguiProssimaChiamata();
        
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
}
