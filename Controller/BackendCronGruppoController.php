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

/**
 * @Route("panel/cron/gruppi")
 * @Sidebar("cron", label="Gestione Cronjob", visible=true, weight=1)
 */
class BackendCronGruppoController extends BaseBackendController
{
    /**
     * @Route("/list", name="app_backend_cron_gruppo_list")
     * @Method({"GET"})
     * @Sidebar("list_gruppi", label="Lista Gruppi", parent="cron", visible=true, weight=1)
     */
    public function listAction(Request $request) {
        
        $pageTitle = 'Lista gruppi chiamate Cronjob';
        
        $tableColumns = array(
            array('title' => 'ID', 'type' => 'number', 'name' => 'id', 'filterable' => true, 'sortable' => true),
            array('title' => 'Nome', 'type' => 'text', 'name' => 'nome', 'filterable' => true, 'sortable' => true),
            array('title' => 'Dipendenza', 'type' => 'text', 'name' => 'dipendenza', 'filterable' => false, 'sortable' => false),
            array('title' => 'Ora min', 'type' => 'text', 'name' => 'oraMin', 'filterable' => false, 'sortable' => false),
            array('title' => 'Ora max', 'type' => 'text', 'name' => 'oraMax', 'filterable' => false, 'sortable' => false),
            array('title' => 'Iterazioni max', 'type' => 'number', 'name' => 'maxIterazioni', 'filterable' => false, 'sortable' => false),
            array('title' => 'Ordine', 'type' => 'number', 'name' => 'weight', 'filterable' => false, 'sortable' => false),
        );

        $defaultSorting = array("weight" => "asc");
        $defaultFilter = array("id" => '', 'nome' => '');

        //Briciole di pane
        $breadcrumb = array(
            ['url' => $this->generateUrl('app_backend_cron_gruppo_list'), "name" => $pageTitle]
        );
        
        return $this->forward('MrappsBackendBundle:Default:__list', array(
            'request' => $request,
            'title'=> $pageTitle,
            'tableColumns' => $tableColumns,
            'defaultSorting'  => $defaultSorting,
            'defaultFilter' => $defaultFilter,
            'linkData' => $this->generateUrl('app_backend_cron_gruppo_data'),
            'linkNew' => $this->generateUrl('app_backend_cron_gruppo_add'),
            'linkEdit' => $this->generateUrl('app_backend_cron_gruppo_edit'),
            'linkDelete' => $this->generateUrl('app_backend_cron_gruppo_delete'),
            'linkOrder' => $this->generateUrl('app_backend_cron_gruppo_order'),
            'linkBreadcrumb' => ['type' => 'url', 'url' => $breadcrumb],
        ));
    }
    
    /**
     * @Route("/data", name="app_backend_cron_gruppo_data")
     * @Method({"GET"})
     */
    public function dataAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        
        $data = BackendUtils::getListResults($em, 'AppBundle:CronConfigGruppo', $request->get('count'), $request->get('page'), $request->get('filter'), $request->get('sorting'));
        
        $output = array();
        foreach ($data as $gruppo) {
            
            $output[] = array(
                'id' => $gruppo->getId(),
                'nome' => $gruppo->getNome(),
                'dipendenza' => ($gruppo->getGruppoDipendente() !== null) ? $gruppo->getGruppoDipendente()->getNome() : '',
                'oraMin' => ($gruppo->getOraMin() !== null) ? $gruppo->getOraMin()->format('H:i') : '',
                'oraMax' => ($gruppo->getOraMax() !== null) ? $gruppo->getOraMax()->format('H:i') : '',
                'maxIterazioni' => ($gruppo->getMaxIterazioni() !== null) ? $gruppo->getMaxIterazioni() : '',
                'weight' => intval($gruppo->getWeight()),
            );
        }
        
        return new JsonResponse($output);
    }
    
    
    /**
     * @Route("/add", name="app_backend_cron_gruppo_add")
     * @Route("/edit/{id}", name="app_backend_cron_gruppo_edit")
     * @Method({"GET"})
     */
    public function editAction(Request $request, $id = null) {
        
        $em = $this->getDoctrine()->getManager();
        
        $form = array(
            'id' => $id,
            'nome' => '',
            'dipendenza' => null,
            'oraMin' => null,
            'oraMax' => null,
            'maxIterazioni' => null,
        );
        
        $gruppo = $em->getRepository('AppBundle:CronConfigGruppo')->find(intval($id));
        if($gruppo !== null) {
            
            //EDIT
            $isEdit = true;
            $pageTitle = sprintf("Modifica gruppo chiamata Cronjob: %s", $gruppo->getNome());
            $filterParams = array('gruppo' => $gruppo->getId());
            
            $form['nome'] = $gruppo->getNome();
            $form['dipendenza'] = ($gruppo->getGruppoDipendente() !== null) ? $gruppo->getGruppoDipendente()->getId() : null;
            $form['oraMin'] = BackendUtils::convertDatetimeToTimeString($gruppo->getOraMin());
            $form['oraMax'] = BackendUtils::convertDatetimeToTimeString($gruppo->getOraMax());
            $form['maxIterazioni'] = ($gruppo->getMaxIterazioni() !== null) ? $gruppo->getMaxIterazioni() : null;
            
        }else {
            
            //ADD
            $isEdit = false;
            $pageTitle = "Aggiunta nuovo gruppo chiamata Cronjob";
            $filterParams = array();
        }
        
        $breadcrumb = array(
            ['url' => $this->generateUrl('app_backend_cron_gruppo_list'), "name" => 'Lista gruppi chiamate Cronjob'],
            ['url' => $this->generateUrl('app_backend_cron_gruppo_edit'), "name" => $pageTitle],
        );

        //Campi form
        $fields = array(
            array('type' => 'hidden', 'name' => 'id', 'value' => $form['id']),
            array('title' => 'Nome', 'type' => 'text', 'name' => 'nome', 'required' => true, 'value' => $form['nome']),
            array('title' => 'Dipendenza', 'type' => 'select', 'name' => 'dipendenza', 'required' => false, 'url' => $this->generateUrl('app_backend_cron_gruppo_ajaxgetdipendenze'), 'filter_params' => $filterParams, 'value' => $form['dipendenza'], 'didascalia' => 'Se si specifica una dipendenza, le chiamate di questo gruppo non verranno eseguite fino a quando non sarà stato completato il gruppo specificato.'),
            array('title' => 'Ora Min', 'type' => 'time', 'name' => 'oraMin', 'required' => false, 'value' => $form['oraMin'], 'didascalia' => "Se specificato, questo gruppo di chiamate verrà eseguito solo a partire da quest'ora."),
            array('title' => 'Ora Max', 'type' => 'time', 'name' => 'oraMax', 'required' => false, 'value' => $form['oraMax'], 'didascalia' => "Se specificato, questo gruppo di chiamate non verrà più eseguito se viene passata quest'ora."),
            array('title' => 'Max iterazioni', 'type' => 'number', 'name' => 'maxIterazioni', 'min' => 0, 'required' => false, 'value' => $form['maxIterazioni'], 'didascalia' => 'Numero massimo di iterazioni giornaliere di questo gruppo.'),
        );

        return $this->forward('MrappsBackendBundle:Default:__new', array(
            'request' => $request,
            'title' => $pageTitle,
            'fields'  => $fields,
            'linkNew' => $this->generateUrl('app_backend_cron_gruppo_add'),
            'linkEdit' => $this->generateUrl('app_backend_cron_gruppo_edit'),
            'linkSave' => $this->generateUrl('app_backend_cron_gruppo_save'),
            'create' => false,
            'edit' => $isEdit,
            'linkBreadcrumb' => ['type' => 'url', 'url' => $breadcrumb],
            'confirmSave' => true,
        ));
    }
    
    /**
     * @Route("/ajax_getdipendenze", name="app_backend_cron_gruppo_ajaxgetdipendenze")
     * @Method({"GET"})
     */
    public function ajaxgetdipendenzeAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $id = intval($request->get('id'));
        $gruppoId = intval($request->get('gruppo'));
        
        $repo = $em->getRepository('AppBundle:CronConfigGruppo');
        
        if($id > 0) {
            
            $gruppo = $repo->find($id);
            $output = $repo->getSelectEntry($gruppo);
            
        }else {
            
            $gruppi = $em->createQuery("
                SELECT ccg
                FROM AppBundle:CronConfigGruppo ccg
                WHERE ccg.id != :gruppo_id
                ORDER BY ccg.weight ASC
            ")->setParameters(array('gruppo_id' => $gruppoId))->execute();
            
            $output = array();
            foreach ($gruppi as $gruppo) {
                $output[] = $repo->getSelectEntry($gruppo);
            }
        }
        
        return new JsonResponse($output);
    }
    
    /**
     * @Route("/save/", name="app_backend_cron_gruppo_save")
     * @Method({"POST"})
     */
    public function saveAction(Request $request) {
        
        $params = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        
        //Salvataggio gruppo
        $response = $em->getRepository('AppBundle:CronConfigGruppo')->editConfigGruppoForm($params);
        
        $success = $response['success'];
        $message = $response['message'];
        $gruppoId = $response['gruppo_id'];
        
        return BackendUtils::generateResponse($success, $message, $gruppoId);
    }
    
    /**
     * @Route("/order", name="app_backend_cron_gruppo_order")
     * @Method({"POST"})
     */
    public function orderAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $params = json_decode($request->getContent(), true);
        $dati = (isset($params['data']) && is_array($params['data'])) ? $params['data'] : array();
        $page = (isset($params['page'])) ? intval($params['page']) : 1;
        $count = (isset($params['count'])) ? intval($params['count']) : 10;
        
        foreach ($dati as $key => $value) {
            $object = $em->getRepository('AppBundle:CronConfigGruppo')->find($value['id']);
            $object->setWeight($key+$count*$page);
            $em->persist($object);
        }
        
        $em->flush();
        
        return new Response('Completato');
    }
    
    /**
     * @Route("/delete", name="app_backend_cron_gruppo_delete")
     * @Method({"POST"})
     */
    public function deleteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $params = json_decode($request->getContent(), true);
        $id = (isset($params['id'])) ? intval($params['id']) : 1;
        
        $gruppo = $em->getRepository('AppBundle:CronConfigGruppo')->find($id);
        
        $result = $em->getRepository('AppBundle:CronConfigGruppo')->deleteGruppo($gruppo);
        $success = $result['success'];
        $message = $result['message'];

        return BackendUtils::generateResponse($success, $message);
    }
}