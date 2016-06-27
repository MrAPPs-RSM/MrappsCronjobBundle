<?php

namespace Mrapps\CronjobBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManager;
use Mrapps\CronjobBundle\Entity\CronConfigGruppo;
use Mrapps\CronjobBundle\Entity\CronLogGruppo;
use Mrapps\CronjobBundle\Entity\CronConfigChiamata;
use Mrapps\CronjobBundle\Entity\CronLogChiamata;

class CronjobHandler
{
    /* @var $container \Symfony\Component\DependencyInjection\Container */
    private $container;
    
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    public function __construct(Container $container) {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
    }
    
    //==========================================================================================
    
    /**
     * Restituisce la lista di gruppi eseguiti oggi
     * 
     * @return array
     */
    public function getLogGruppiEseguitiOggi() {
        
        $listaGruppi = array();
        
        $now = new \DateTime();
        
        //Query nativa: estrae la lista di ID dei gruppi eseguiti oggi
        $nowString = $now->format('Y-m-d').' 00:00:00';
        $records = $this->em->getConnection()->executeQuery('
            SELECT DISTINCT gruppo_id
            FROM mrapps_cronjob_log_gruppo
            WHERE created_at >= "'.$nowString.'"
            ORDER BY created_at DESC
        ')->fetchAll();
        
        //Per ogni gruppo estrae l'ultima riga di log
        foreach ($records as $r) {
            
            $gruppo = $this->em->getRepository('MrappsCronjobBundle:CronConfigGruppo')->find(intval($r['gruppo_id']));
            if($gruppo !== null) {
                
                /* @var $logGruppo \Mrapps\CronjobBundle\Entity\CronLogGruppo */
                $logGruppo = $this->em->createQuery("
                    SELECT clg
                    FROM MrappsCronjobBundle:CronLogGruppo clg
                    WHERE clg.gruppo = :gruppo
                    ORDER BY clg.createdAt DESC
                ")->setMaxResults(1)->setParameter('gruppo', $gruppo)->getOneOrNullResult();
                
                if($logGruppo !== null) {
                    $listaGruppi[] = $logGruppo;
                }
            }
        }
        
        return $listaGruppi;
    }
    
    /**
     * Restituisce l'ultimo gruppo eseguito oggi
     * 
     * @param array $gruppiEseguitiOggi
     * @return CronLogGruppo
     */
    public function getUltimoLogGruppo(array $gruppiEseguitiOggi) {
        
        $maxTs = 0;
        $maxIndex = -1;
        
        /* @var $logGruppo \Mrapps\CronjobBundle\Entity\CronLogGruppo */
        foreach ($gruppiEseguitiOggi as $key => $logGruppo) {
            
            //Cerca la riga di log col Timestamp maggiore
            if($logGruppo->getCreatedAt()->getTimestamp() > $maxTs) {
                $maxTs = $logGruppo->getCreatedAt()->getTimestamp();
                $maxIndex = $key;
            }
        }
        
        return (isset($gruppiEseguitiOggi[$maxIndex])) ? $gruppiEseguitiOggi[$maxIndex] : null;
    }
    
    /**
     * Controlla se il gruppo passato come parametro rispetta le condizioni per essere il prossimo gruppo da eseguire
     * 
     * @param CronConfigGruppo $candidato
     * @param array $gruppiEseguitiOggi
     * @return bool
     */
    public function checkGruppoRispettaCondizioni(CronConfigGruppo $candidato, array $gruppiEseguitiOggi) {
        
        $dipendenza = $candidato->getGruppoDipendente();
        $maxIterazioni = $candidato->getMaxIterazioni();
        
        $logCandidato = null;
        $logDipendenza = null;
        
        /* @var $logGruppo \Mrapps\CronjobBundle\Entity\CronLogGruppo */
        foreach ($gruppiEseguitiOggi as $logGruppo) {
            
            if($logGruppo->getGruppo() !== null) {
                
                //Riga di Log del gruppo candidato
                if($logCandidato == null && $logGruppo->getGruppo()->getId() == $candidato->getId()) {
                    $logCandidato = $logGruppo;
                }
                
                //Riga di Log del gruppo dipendenza (se c'è)
                if($logDipendenza == null && $dipendenza !== null && $logGruppo->getGruppo()->getId() == $dipendenza->getId()) {
                    $logDipendenza = $logGruppo;
                }
            }
        }
        
        //Il controllo sulla dipendenza passa sempre, tranne nel caso in cui il gruppo ha una dipendenza ma non c'è la corrispondente riga di log
        $resultDipendenza = !($dipendenza !== null && $logDipendenza == null);

        //Numero della prossima iterazione
        $prossimaIterazione = ($logCandidato !== null) ? $logCandidato->getIterazione()+1 : 1;
        
        //Se il massimo numero di iterazioni è NULL non controllo niente, altrimenti il controllo fallisce solo se la prossima iterazione sarebbe successiva
        $resultMaxIterazioni = ($maxIterazioni !== null) ? ($maxIterazioni >= $prossimaIterazione) : true;

        //Il risultato è la combinazione di tutti i controlli
        return ($resultDipendenza && $resultMaxIterazioni);
    }
    
    /**
     * Restituisce la lista di gruppi candidati a diventare il prossimo gruppo da eseguire
     * 
     * @param CronConfigGruppo $gruppoPrecedente
     * @return array
     */
    public function getListaCandidati(CronConfigGruppo $gruppoPrecedente = null) {
        
        $now = new \DateTime();
        
        $where = " (ccg.oraMin <= :oraMin OR ccg.oraMin IS NULL) AND (ccg.oraMax >= :oraMax OR ccg.oraMax IS NULL) ";
        $params = array(
            'oraMin' => $now,
            'oraMax' => $now,
        );
        
        //Salvo la query originale in caso non venisse estratto nessun candidato
        $originalWhere = $where;
        $originalParams = $params;
        
        if($gruppoPrecedente !== null) {
            $params['weight'] = $gruppoPrecedente->getWeight();
            $where .= " AND ccg.weight > :weight ";
        }
        
        $query = "
            SELECT ccg
            FROM MrappsCronjobBundle:CronConfigGruppo ccg
            WHERE %s
            ORDER BY ccg.weight ASC
        ";
        
        $listaCandidati = $this->em->createQuery(sprintf($query, $where))->setParameters($params)->execute();
            
        //Nessun candidato estratto? Forse semplicemente siamo arrivati alla fine della lista. Rifaccio la chiamata senza controllo sul peso
        if(count($listaCandidati) == 0) {
            if(isset($params['weight'])) unset($params['weight']);
            $listaCandidati = $this->em->createQuery(sprintf($query, $originalWhere))->setParameters($originalParams)->execute();
        }
            
        return $listaCandidati;
    }
    
    /**
     * Restituisce il candidato finale dalla lista di candidati passata come parametro
     * 
     * @param array $listaCandidati
     * @param array $gruppiEseguitiOggi
     * @return CronConfigGruppo
     */
    public function getCandidatoFinaleGruppo(array $listaCandidati, array $gruppiEseguitiOggi) {
        
        $candidatoFinale = null;
        
        //Cicla i candidati
        foreach ($listaCandidati as $candidato) {
            
            //Se un candidato rispetta le condizioni viene scelto, altrimenti si passa al prossimo
            $rispettaCondizioni = $this->checkGruppoRispettaCondizioni($candidato, $gruppiEseguitiOggi);
            if($rispettaCondizioni) {
                $candidatoFinale = $candidato;
                break;
            }
        }
        
        return $candidatoFinale;
    }
    
    /**
     * Restituisce il gruppo corrente da eseguire
     * 
     * @return CronConfigGruppo
     */
    public function getGruppoCorrente() {
        
        //Gruppi eseguiti oggi
        $gruppiEseguitiOggi = $this->getLogGruppiEseguitiOggi();
        
        //Ultimo gruppo eseguito oggi
        $logGruppoPrecedente = $this->getUltimoLogGruppo($gruppiEseguitiOggi);
        $ultimoGruppo = ($logGruppoPrecedente !== null) ? $logGruppoPrecedente->getGruppo() : null;
        
        //Se l'ultimo gruppo non è ancora stato completato restituisce questo
        if($logGruppoPrecedente !== null && $logGruppoPrecedente->getCompletato() != true) {
            return $ultimoGruppo;
        }

        //Lista dei gruppi candidati a diventare il prossimo ad essere eseguito
        $listaCandidati = $this->getListaCandidati($ultimoGruppo);

        //Restituisce il primo candidato che rispetta le condizioni
        $candidatoFinale = $this->getCandidatoFinaleGruppo($listaCandidati, $gruppiEseguitiOggi);

        return $candidatoFinale;
    }
    
    /**
     * Ottiene la prossima iterazione per il gruppo corrente
     * 
     * @param CronConfigGruppo $gruppo
     */
    public function getProssimaIterazione(CronConfigGruppo $gruppo) {
        
        $params = array('gruppo' => $gruppo);
        
        $ultimoGruppo = $this->em->createQuery("
            SELECT clg
            FROM MrappsCronjobBundle:CronLogGruppo clg
            WHERE clg.gruppo = :gruppo
            ORDER BY clg.createdAt DESC
        ")->setMaxResults(1)->setParameters($params)->getOneOrNullResult();
        
        $iterazione = ($ultimoGruppo !== null) ? $ultimoGruppo->getIterazione() : 1;

        return $iterazione;
    }
    
    /**
     * Scrivi log per il gruppo corrente
     * 
     * @param CronConfigGruppo $gruppo
     * @return CronLogGruppo
     */
    public function scriviLogGruppo(CronConfigGruppo $gruppo) {
        
        $prossimaIterazione = $this->getProssimaIterazione($gruppo);
        
        $logGruppo = new CronLogGruppo();
        $logGruppo->setCompletato(false);
        $logGruppo->setGruppo($gruppo);
        $logGruppo->setIterazione($prossimaIterazione);
        $logGruppo->setVisible(true);
        
        $this->em->persist($logGruppo);
        $this->em->flush();
        
        return $logGruppo;
    }
    
    /**
     * Completa il gruppo corrente
     * 
     * @param CronLogGruppo $logGruppo
     */
    public function completaLogGruppo(CronLogGruppo $logGruppo) {
        
        $logGruppo->setCompletato(true);
        $this->em->persist($logGruppo);
        $this->em->flush();
    }
    
    //==========================================================================================
    
    /**
     * Restituisce l'ultima chiamata eseguita (o in esecuzione in questo momento)
     * 
     * @return CronLogChiamata
     */
    public function getUltimoLogChiamata() {
        
        /* @var $ultimoLogChiamata \Mrapps\CronjobBundle\Entity\CronLogChiamata */
        $ultimoLogChiamata = $this->em->createQuery("
            SELECT clc
            FROM MrappsCronjobBundle:CronLogChiamata clc
            ORDER BY clc.createdAt DESC
        ")->setMaxResults(1)->getOneOrNullResult();
        
        if($ultimoLogChiamata !== null) {
            
            $mezzanotte = new \DateTime();
            $mezzanotte->setTime(0, 0, 0);
            
            //Se l'ultima chiamata è di ieri ed è completata, restituisco NULL (in modo da ripartire col nuovo giorno)
            if($ultimoLogChiamata->getCreatedAt() < $mezzanotte && $ultimoLogChiamata->getLogDataInizio() == null) {
                $ultimoLogChiamata = null;
            }
        }
        
        return $ultimoLogChiamata;
    }
    
    /**
     * Restituisce la prossima chiamata nel gruppo
     * 
     * @param CronConfigChiamata $chiamataAttuale
     * @return CronConfigChiamata
     */
    public function getChiamataSuccessiva(CronConfigChiamata $chiamataAttuale) {
        
        $originalParams = array(
            'gruppo' => $chiamataAttuale->getGruppo(),
        );
        $params = array_merge($originalParams, array('weight' => $chiamataAttuale->getWeight()));
        
        $originalWhere = " ccc.gruppo = :gruppo ";
        $where = $originalWhere." AND ccc.weight > :weight ";
        
        $query = "
            SELECT ccc
            FROM MrappsCronjobBundle:CronConfigChiamata ccc
            WHERE %s
            ORDER BY ccc.createdAt ASC
        ";
        
        $chiamataSuccessiva = $this->em->createQuery(sprintf($query, $where))->setMaxResults(1)->setParameters($params)->getOneOrNullResult();
        $completaLogGruppo = false;
        
        if($chiamataSuccessiva == null) {
            $chiamataSuccessiva = $this->em->createQuery(sprintf($query, $originalWhere))->setMaxResults(1)->setParameters($originalParams)->getOneOrNullResult();
            $completaLogGruppo = true;
        }
        
        return array(
            'prossima_chiamata' => $chiamataSuccessiva,
            'completa_log_gruppo' => $completaLogGruppo,
        );
    }
    
    /**
     * Controlla se il numero di tentativi massimo è stato raggiunto o meno
     * 
     * @param CronLogChiamata $logChiamata
     * @return bool
     */
    public function checkNumeroTentativiRaggiunto(CronLogChiamata $logChiamata) {
        
        $chiamata = $logChiamata->getChiamata();
        $tentativo = $logChiamata->getTentativo();
        $maxTentativi = $chiamata->getMaxTentativi();
        
        return ($tentativo >= $maxTentativi);
    }
    
    /**
     * Restituisce la prima chiamata per il gruppo passato come parametro
     * 
     * @param CronConfigGruppo $gruppo
     * @return CronConfigChiamata
     */
    public function getPrimaChiamataPerGruppo(CronConfigGruppo $gruppo) {
        
        $params = array('gruppo' => $gruppo);
        
        $primaChiamata = $this->em->createQuery("
            SELECT ccc
            FROM MrappsCronjobBundle:CronConfigChiamata ccc
            WHERE ccc.gruppo = :gruppo
            ORDER BY ccc.weight ASC
        ")->setMaxResults(1)->setParameters($params)->getOneOrNullResult();
        
        return $primaChiamata;
    }
    
    /**
     * Restituisce la prossima chiamata da eseguire
     * 
     * @return CronConfigChiamata
     */
    public function getProssimaChiamata() {
        
        $prossimaChiamata = null;
        $tentativo = 1;
        $ultimoLogGruppo = null;
        
        //Gruppo in esecuzione al momento
        
        /* @var $gruppoCorrente \Mrapps\CronjobBundle\Entity\CronConfigGruppo */
        $gruppoCorrente = $this->getGruppoCorrente();
        
        if($gruppoCorrente !== null) {
            
            //Ultima chiamata loggata
            
            /* @var $ultimoLogChiamata \Mrapps\CronjobBundle\Entity\CronLogChiamata */
            $ultimoLogChiamata = $this->getUltimoLogChiamata();
        
            if($ultimoLogChiamata !== null) {
                
                //Gruppo dell'ultima chiamata eseguita
                
                /* @var $gruppoUltimaChiamata \Mrapps\CronjobBundle\Entity\CronConfigGruppo */
                $gruppoUltimaChiamata = $ultimoLogChiamata->getChiamata()->getGruppo();
                
                //Il gruppo dell'ultima chiamata corrisponde al gruppo in esecuzione al momento?
                if($gruppoUltimaChiamata->getId() == $gruppoCorrente->getId()) {
                    
                    //Ultimo Log Gruppo
                    $ultimoLogGruppo = $ultimoLogChiamata->getLogGruppo();
                    
                    //Se Log Data Inizio è diverso da NULL, la chiamata è ancora in corso e non restituisco niente
                    if($ultimoLogChiamata->getLogDataInizio() == null) {
                        
                        if($ultimoLogChiamata->getSuccess() == true) {
                            
                            //Prossima chiamata nel gruppo
                            $datiChiamataSuccessiva = $this->getChiamataSuccessiva($ultimoLogChiamata->getChiamata());
                            
                            $prossimaChiamata = $datiChiamataSuccessiva['prossima_chiamata'];
                            $completaLogGruppo = $datiChiamataSuccessiva['completa_log_gruppo'];
                            
                            //Siamo arrivati alla fine del gruppo?
                            if($completaLogGruppo) {
                                $this->completaLogGruppo($ultimoLogChiamata->getLogGruppo());
                                $ultimoLogGruppo = $this->scriviLogGruppo($gruppoCorrente);
                            }
                            
                        }else {
                            
                            //Posso procedere con i tentativi o sono arrivato alla fine?
                            $numeroTentativiRaggiunto = $this->checkNumeroTentativiRaggiunto($ultimoLogChiamata);
                            
                            if($numeroTentativiRaggiunto == false) {
                                
                                //Ritento la stessa chiamata
                                $prossimaChiamata = $ultimoLogChiamata->getChiamata();
                                $tentativo = $ultimoLogChiamata->getTentativo()+1;
                                
                            }else {
                                
                                /**
                                 * @TODO EMERGENZA
                                 * Se la chiamata arriva qui c'è un problema grave e il meccanismo dovrebbe interrompersi.
                                 * Probabilmente bisognerebbe inviare una mail a noi stessi e vedere se fare altro (es. mettere il sito in manutenzione?)
                                 */
                                
                                $prossimaChiamata = null;
                                $tentativo = 0;
                                $ultimoLogGruppo = null;
                            }
                        }
                    }
                    
                }else {
                    //Se il gruppo corrente è diverso dal gruppo dell'ultima chiamata -> prendo la prima chiamata del gruppo corrente
                    $prossimaChiamata = $this->getPrimaChiamataPerGruppo($gruppoCorrente);
                    
                    //Scrittura Log gruppo
                    $ultimoLogGruppo = $this->scriviLogGruppo($gruppoCorrente);
                }

            }else {
                
                //Se non ci sono chiamate loggate -> prendo la prima chiamata del gruppo corrente
                $prossimaChiamata = $this->getPrimaChiamataPerGruppo($gruppoCorrente);
                
                //Scrittura Log gruppo
                $ultimoLogGruppo = $this->scriviLogGruppo($gruppoCorrente);
            }
        }
        
        return array(
            'prossima_chiamata' => $prossimaChiamata,
            'tentativo' => $tentativo,
            'ultimo_log_gruppo' => $ultimoLogGruppo,
        );
    }
    
    /**
     * Scrivi log per la chiamata corrente
     * 
     * @param CronConfigChiamata $chiamata
     * @param int $tentativo
     * @param CronLogGruppo $logGruppo
     * @return CronLogChiamata
     */
    public function scriviLogChiamata(CronConfigChiamata $chiamata, $tentativo, CronLogGruppo $logGruppo = null) {
        
        $now = new \DateTime();
        
        $logChiamata = new CronLogChiamata();
        $logChiamata->setChiamata($chiamata);
        $logChiamata->setTentativo($tentativo);
        $logChiamata->setLogDataInizio($now);
        $logChiamata->setLogGruppo($logGruppo);
        $logChiamata->setOutput(null);
        $logChiamata->setSuccess(null);
        $logChiamata->setVisible(true);
        
        $this->em->persist($logChiamata);
        $this->em->flush();
        
        return $logChiamata;
    }
    
    /**
     * Completa la chiamata corrente
     * 
     * @param CronLogChiamata $logChiamata
     * @param bool $success
     * @param string $output
     */
    public function completaLogChiamata(CronLogChiamata $logChiamata, $success, $output) {
        
        $logChiamata->setLogDataInizio(null);
        $logChiamata->setSuccess($success);
        $logChiamata->setOutput($output);
        
        $this->em->persist($logChiamata);
        $this->em->flush();
    }
    
    //==========================================================================================
    
    /**
     * Esecuzione di una chiamata di tipo CLASSE
     * 
     * @param CronConfigChiamata $prossimaChiamata
     * @param type $tentativo
     * @param CronLogGruppo $ultimoLogGruppo
     *
     * @return array
     */
    public function eseguiChiamataClasse(CronConfigChiamata $prossimaChiamata, $tentativo, CronLogGruppo $ultimoLogGruppo = null) {
        
        //Classe da eseguire
        $classe = str_replace('\\\\', '\\', $prossimaChiamata->getEndpoint());

        //La classe esiste?
        if(class_exists($classe)) {

            //Parametri aggiuntivi
            $parametri = array();
            parse_str($prossimaChiamata->getParametri(), $parametri);

            //Tenta la creazione dell'oggetto della classe specificata

            /* @var $object \Mrapps\CronjobBundle\Model\CronjobInterface */
            $object = new $classe;

            //L'oggetto è ciò che ci aspettiamo?
            if($object !== null && is_a($object, 'Mrapps\CronjobBundle\Model\CronjobInterface')) {

                //Inizio log
                $logChiamata = $this->scriviLogChiamata($prossimaChiamata, $tentativo, $ultimoLogGruppo);

                //Esegue la chiamata e attende l'output
                
                /* @var $cronResponse \Mrapps\CronjobBundle\Model\CronjobResponse */
                $cronResponse = $object->run($this->container, $parametri);
                if(is_a($cronResponse, 'Mrapps\CronjobBundle\Model\CronjobResponse')) {
                    
                    $success = $cronResponse->isSuccessful();
                    $output = $cronResponse->getResponse();
                    
                }else {
                    $success = false;
                    $output = 'La classe deve ritornare un oggetto di tipo CronjobResponse.';
                }

                //Fine log
                $this->completaLogChiamata($logChiamata, $success, $cronResponse);

            }else {
                $success = false;
                $output = 'INTERNAL: la classe specificata non implementa CronjobInterface.';
            }

        }else {
            $success = false;
            $output = 'INTERNAL: la classe specificata non esiste.';
        }
        
        return array(
            'success' => $success,
            'output' => $output,
        );
    }
    
    
    /**
     * Esecuzione di una chiamata di tipo URL
     * 
     * @param CronConfigChiamata $prossimaChiamata
     * @param type $tentativo
     * @param CronLogGruppo $ultimoLogGruppo
     *
     * @return array
     */
    public function eseguiChiamataUrl(CronConfigChiamata $prossimaChiamata, $tentativo, CronLogGruppo $ultimoLogGruppo = null) {
        
        $url = $prossimaChiamata->getEndpoint();
        
        //Parametri aggiuntivi
        $parametri = array();
        parse_str($prossimaChiamata->getParametri(), $parametri);
        
        if(count($parametri) > 0) {
            $queryString = http_build_query($parametri);
            $url .= $queryString;
        }
        
        //Inizio log
        $logChiamata = $this->scriviLogChiamata($prossimaChiamata, $tentativo, $ultimoLogGruppo);
        
        //Chiamata CURL
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl_handle);
        if($result === false) {
            $success = false;
            $output = curl_error($curl_handle);
        }else {
            $success = true;
            $output = $result;
        }
        curl_close($curl_handle);
        
        //Fine log
        $this->completaLogChiamata($logChiamata, $success, $output);
        
        return array(
            'success' => $success,
            'output' => $output,
        );
    }
    
    /**
     * Esecuzione della prossima chiamata
     * 
     * @return type
     */
    public function eseguiProssimaChiamata() {
        
        $success = false;
        $output = 'INTERNAL: errore sconosciuto.';
        $logChiamata = null;
        
        try {
            
            $dati = $this->getProssimaChiamata();
        
            /* @var $prossimaChiamata \Mrapps\CronjobBundle\Entity\CronConfigChiamata */
            $prossimaChiamata = $dati['prossima_chiamata'];

            $tentativo = $dati['tentativo'];

            /* @var $ultimoLogGruppo \Mrapps\CronjobBundle\Entity\CronLogGruppo */
            $ultimoLogGruppo = $dati['ultimo_log_gruppo'];

            if($prossimaChiamata !== null) {
                
                switch($prossimaChiamata->getTipoChiamata()) {
                    case 'CLASSE':
                        $result = $this->eseguiChiamataClasse($prossimaChiamata, $tentativo, $ultimoLogGruppo);
                        break;
                    case 'URL':
                        $result = $this->eseguiChiamataUrl($prossimaChiamata, $tentativo, $ultimoLogGruppo);
                        break;
                    default:
                        $result = null;
                        break;
                }
                
                if($result !== null) {
                    $success = $result['success'];
                    $output = $result['output'];
                }
            }
            
        } catch (\Exception $ex) {
            $success = false;
            $output = 'INTERNAL: '.$ex->getMessage();
        }
        
        return array('success' => $success, 'output' => $output, 'log_chiamata' => $logChiamata);
    }
}