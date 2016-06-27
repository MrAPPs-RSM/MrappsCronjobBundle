<?php

namespace Mrapps\CronjobBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Mrapps\BackendBundle\Controller\BaseBackendController;
use Mrapps\BackendBundle\Annotation\Sidebar;
use Mrapps\BackendBundle\Classes\Utils as BackendUtils;
use Mrapps\CronjobBundle\Entity\CronConfigChiamata;

/**
 * @Route("panel/mrapps_cronjob/chiamate")
 */
class BackendCronChiamataController extends BaseBackendController
{
    /**
     * @Route("/list", name="mrapps_cronjob_backend_cron_chiamata_list")
     * @Method({"GET"})
     * @Sidebar("list_chiamate", label="Lista Chiamate", parent="mrapps_cron", visible=true, weight=2)
     */
    public function listAction(Request $request) {
        
        $pageTitle = 'Lista chiamate Cronjob';
        
        $tableColumns = array(
            array('title' => 'ID', 'type' => 'number', 'name' => 'id', 'filterable' => true, 'sortable' => true),
            array('title' => 'Gruppo', 'type' => 'text', 'name' => 'gruppo', 'filterable' => true, 'sortable' => true),
            array('title' => 'Tipo', 'type' => 'text', 'name' => 'tipoChiamata', 'filterable' => true, 'sortable' => true),
            array('title' => 'Endpoint', 'type' => 'text', 'name' => 'endpoint', 'filterable' => true, 'sortable' => true),
            array('title' => 'Parametri', 'type' => 'text', 'name' => 'parametri', 'filterable' => true, 'sortable' => true),
            array('title' => 'Descrizione', 'type' => 'text', 'name' => 'descrizione', 'filterable' => true, 'sortable' => true),
            array('title' => 'Tentativi max', 'type' => 'number', 'name' => 'maxTentativi', 'filterable' => true, 'sortable' => true),
            array('title' => 'Ordine', 'type' => 'number', 'name' => 'weight', 'filterable' => false, 'sortable' => false),
        );

        $defaultSorting = array("weight" => "asc");
        $defaultFilter = array("id" => '');

        //Briciole di pane
        $breadcrumb = array(
            ['url' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_list'), "name" => $pageTitle]
        );
        
        return $this->forward('MrappsBackendBundle:Default:__list', array(
            'request' => $request,
            'title'=> $pageTitle,
            'tableColumns' => $tableColumns,
            'defaultSorting'  => $defaultSorting,
            'defaultFilter' => $defaultFilter,
            'linkData' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_data'),
            'linkNew' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_add'),
            'linkEdit' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_edit'),
            'linkDelete' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_delete'),
            'linkOrder' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_order'),
            'linkBreadcrumb' => ['type' => 'url', 'url' => $breadcrumb],
        ));
    }
    
    /**
     * @Route("/data", name="mrapps_cronjob_backend_cron_chiamata_data")
     * @Method({"GET"})
     */
    public function dataAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        
        $data = BackendUtils::getListResults($em, 'MrappsCronjobBundle:CronConfigChiamata', $request->get('count'), $request->get('page'), $request->get('filter'), $request->get('sorting'));
        
        $output = array();
        
        foreach ($data as $chiamata) {
            
            $output[] = array(
                'id' => $chiamata->getId(),
                'gruppo' => ($chiamata->getGruppo() !== null) ? $chiamata->getGruppo()->getNome() : '',
                'tipoChiamata' => $chiamata->getTipoChiamata(),
                'endpoint' => $chiamata->getEndpoint(),
                'parametri' => $chiamata->getParametri(),
                'descrizione' => $chiamata->getDescrizione(),
                'maxTentativi' => $chiamata->getMaxTentativi(),
                'weight' => intval($chiamata->getWeight()),
            );
        }
        
        return new JsonResponse($output);
    }
    
    
    /**
     * @Route("/add", name="mrapps_cronjob_backend_cron_chiamata_add")
     * @Route("/edit/{id}", name="mrapps_cronjob_backend_cron_chiamata_edit")
     * @Method({"GET"})
     */
    public function editAction(Request $request, $id = null) {
        
        $em = $this->getDoctrine()->getManager();
        
        $form = array(
            'id' => $id,
            'gruppo' => null,
            'tipo' => null,
            'endpoint' => '',
            'parametri' => '',
            'descrizione' => '',
            'max_tentativi' => 3,
        );
        
        /* @var $chiamata \Mrapps\CronjobBundle\Entity\CronConfigChiamata */
        $chiamata = $em->getRepository('MrappsCronjobBundle:CronConfigChiamata')->find(intval($id));
        if($chiamata !== null) {
            
            //EDIT
            $isEdit = true;
            $pageTitle = sprintf("Modifica chiamata Cronjob: %s", $chiamata->getDescrizione());
            
            $form['tipo'] = $chiamata->getTipoChiamata();
            $form['gruppo'] = ($chiamata->getGruppo() !== null) ? $chiamata->getGruppo()->getId() : null;
            $form['endpoint'] = $chiamata->getEndpoint();
            $form['parametri'] = $chiamata->getParametri();
            $form['descrizione'] = $chiamata->getDescrizione();
            $form['max_tentativi'] = $chiamata->getMaxTentativi();
            
        }else {
            
            //ADD
            $isEdit = false;
            $pageTitle = "Aggiunta nuova chiamata Cronjob";
        }
        
        $breadcrumb = array(
            ['url' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_list'), "name" => 'Lista chiamate Cronjob'],
            ['url' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_edit'), "name" => $pageTitle],
        );

        //Campi form
        $fields = array(
            array('type' => 'hidden', 'name' => 'id', 'value' => $form['id']),
            array('title' => 'Gruppo', 'type' => 'select', 'name' => 'gruppo', 'required' => true, 'url' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_ajaxgetgruppi'), 'value' => $form['gruppo']),
            array('title' => 'Tipo', 'type' => 'select', 'name' => 'tipo', 'required' => true, 'url' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_ajaxgettipi'), 'value' => $form['tipo']),
            array('title' => 'Endpoint', 'type' => 'text', 'name' => 'endpoint', 'required' => true, 'value' => $form['endpoint'], 'didascalia' => 'Specificare l\'endpoint (Classe completa di Namespace oppure URL). La classe deve implementare CronjobInterface.'),
            array('title' => 'Parametri', 'type' => 'text', 'name' => 'parametri', 'required' => false, 'value' => $form['parametri'], 'didascalia' => 'es. myparam=test&otherparam=test2'),
            array('title' => 'Descrizione', 'type' => 'text', 'name' => 'descrizione', 'required' => false, 'value' => $form['descrizione']),
            array('title' => 'Max tentativi', 'type' => 'number', 'name' => 'max_tentativi', 'min' => 1, 'required' => true, 'value' => $form['max_tentativi']),
        );

        return $this->forward('MrappsBackendBundle:Default:__new', array(
            'request' => $request,
            'title' => $pageTitle,
            'fields'  => $fields,
            'linkNew' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_add'),
            'linkEdit' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_edit'),
            'linkSave' => $this->generateUrl('mrapps_cronjob_backend_cron_chiamata_save'),
            'create' => false,
            'edit' => $isEdit,
            'linkBreadcrumb' => ['type' => 'url', 'url' => $breadcrumb],
            'confirmSave' => true,
        ));
    }
    
    /**
     * @Route("/ajax_getgruppi", name="mrapps_cronjob_backend_cron_chiamata_ajaxgetgruppi")
     * @Method({"GET"})
     */
    public function ajaxgetgruppiAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $id = intval($request->get('id'));
        
        $repo = $em->getRepository('MrappsCronjobBundle:CronConfigGruppo');
        
        if($id > 0) {
            
            $gruppo = $repo->find($id);
            $output = $repo->getSelectEntry($gruppo);
            
        }else {
            
            $gruppi = $repo->findBy(array(), array('weight' => 'asc'));
            
            $output = array();
            foreach ($gruppi as $gruppo) {
                $output[] = $repo->getSelectEntry($gruppo);
            }
        }
        
        return new JsonResponse($output);
    }
    
    /**
     * @Route("/ajax_gettipi", name="mrapps_cronjob_backend_cron_chiamata_ajaxgettipi")
     * @Method({"GET"})
     */
    public function ajaxgettipiAction(Request $request) {
        
        $tipi = CronConfigChiamata::TIPI;
        
        $id = trim($request->get('id'));
        
        if(strlen($id) > 0 && in_array($id, $tipi)) {
            $output = array('value' => $id, 'name' => $id);
        }else {
            $output = array();
            foreach ($tipi as $t) {
                $output[] = array('value' => $t, 'name' => $t);
            }
        }
        
        return new JsonResponse($output);
    }
    
    
    /**
     * @Route("/save/", name="mrapps_cronjob_backend_cron_chiamata_save")
     * @Method({"POST"})
     */
    public function saveAction(Request $request) {
        
        $params = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        
        //Salvataggio chiamata
        $response = $em->getRepository('MrappsCronjobBundle:CronConfigChiamata')->editConfigChiamataForm($params);
        
        $success = $response['success'];
        $message = $response['message'];
        $chiamataId = $response['chiamata_id'];
        
        return BackendUtils::generateResponse($success, $message, $chiamataId);
    }
    
    /**
     * @Route("/order", name="mrapps_cronjob_backend_cron_chiamata_order")
     * @Method({"POST"})
     */
    public function orderAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $params = json_decode($request->getContent(), true);
        $dati = (isset($params['data']) && is_array($params['data'])) ? $params['data'] : array();
        $page = (isset($params['page'])) ? intval($params['page']) : 1;
        $count = (isset($params['count'])) ? intval($params['count']) : 10;
        
        foreach ($dati as $key => $value) {
            $object = $em->getRepository('MrappsCronjobBundle:CronConfigChiamata')->find($value['id']);
            $object->setWeight($key+$count*$page);
            $em->persist($object);
        }
        
        $em->flush();
        
        return new Response('Completato');
    }
    
    /**
     * @Route("/delete", name="mrapps_cronjob_backend_cron_chiamata_delete")
     * @Method({"POST"})
     */
    public function deleteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $params = json_decode($request->getContent(), true);
        $id = (isset($params['id'])) ? intval($params['id']) : 1;
        
        $chiamata = $em->getRepository('MrappsCronjobBundle:CronConfigChiamata')->find($id);
        $em->remove($chiamata);
        $em->flush();
        
        return BackendUtils::generateResponse(true, 'Chiamata eliminata');
    }
}