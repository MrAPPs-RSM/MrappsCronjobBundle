<?php

namespace Mrapps\CronjobBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Mrapps\CronjobBundle\Services\CronjobHandler;
use Mrapps\CronjobBundle\Entity\CronLogGruppo;
use Mrapps\CronjobBundle\Entity\CronConfigGruppo;

class CronjobHandlerTest extends WebTestCase
{
    private $service;
    
    protected function setUp() {
        
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        
        $this->service = new CronjobHandler($container);
    }
    
    private function getBaseData() {
        
        //DateTime 8:30 AM
        $dt_08_30 = new \DateTime();
        $dt_08_30->setTime(8, 30, 0);
        
        //DateTime 10:00 AM
        $dt_10_00 = new \DateTime();
        $dt_10_00->setTime(10, 0, 0);
        
        //DateTime 11:40 AM
        $dt_11_40 = new \DateTime();
        $dt_11_40->setTime(11, 40, 0);
        
        //---------------------------------------------------------
        
        //Gruppo 1
        $gruppo_1 = new CronConfigGruppo();
        $gruppo_1->setNome("1");
        $gruppo_1->setMaxIterazioni(2);
        $gruppo_1->setGruppoDipendente(null);
        $gruppo_1->setWeight(1);
        
        //Setta l'ID tramite Reflection
        $objGruppo1 = new \ReflectionObject($gruppo_1);
        $idPropGruppo1 = $objGruppo1->getProperty('id');
        $idPropGruppo1->setAccessible(true);
        $idPropGruppo1->setValue($gruppo_1, 1);
        
        //Gruppo 2
        $gruppo_2 = new CronConfigGruppo();
        $gruppo_2->setNome("2");
        $gruppo_2->setMaxIterazioni(3);
        $gruppo_2->setGruppoDipendente($gruppo_1);
        $gruppo_2->setWeight(2);
        
        //Setta l'ID tramite Reflection
        $objGruppo2 = new \ReflectionObject($gruppo_2);
        $idPropGruppo2 = $objGruppo2->getProperty('id');
        $idPropGruppo2->setAccessible(true);
        $idPropGruppo2->setValue($gruppo_2, 2);
        
        //Gruppo 3
        $gruppo_3 = new CronConfigGruppo();
        $gruppo_3->setNome("3");
        $gruppo_3->setMaxIterazioni(1);
        $gruppo_3->setGruppoDipendente(null);
        $gruppo_3->setWeight(3);
        
        //Setta l'ID tramite Reflection
        $objGruppo3 = new \ReflectionObject($gruppo_3);
        $idPropGruppo3 = $objGruppo3->getProperty('id');
        $idPropGruppo3->setAccessible(true);
        $idPropGruppo3->setValue($gruppo_3, 3);
        
        //Gruppo 4
        $gruppo_4 = new CronConfigGruppo();
        $gruppo_4->setNome("4");
        $gruppo_4->setMaxIterazioni(1);
        $gruppo_4->setGruppoDipendente($gruppo_3);
        $gruppo_4->setWeight(4);
        
        //Setta l'ID tramite Reflection
        $objGruppo4 = new \ReflectionObject($gruppo_4);
        $idPropGruppo4 = $objGruppo4->getProperty('id');
        $idPropGruppo4->setAccessible(true);
        $idPropGruppo4->setValue($gruppo_4, 4);
        
        //---------------------------------------------------------
        
        //Riga di log alle 8:30 AM
        $log_08_30 = new CronLogGruppo();
        $log_08_30->setGruppo($gruppo_1);
        $log_08_30->setIterazione(1);
        $log_08_30->setCompletato(1);
        $log_08_30->setCreatedAt($dt_08_30);
        
        //Riga di log alle 10:00 AM
        $log_10_00 = new CronLogGruppo();
        $log_10_00->setGruppo($gruppo_1);
        $log_10_00->setIterazione(2);
        $log_10_00->setCompletato(1);
        $log_10_00->setCreatedAt($dt_10_00);
        
        //Riga di log alle 11:40 AM
        $log_11_40 = new CronLogGruppo();
        $log_11_40->setGruppo($gruppo_2);
        $log_11_40->setIterazione(1);
        $log_11_40->setCompletato(1);
        $log_11_40->setCreatedAt($dt_11_40);
        
        $gruppiEseguitiOggi = array($log_11_40, $log_10_00, $log_08_30);
        
        return array(
            'dt_08_30' => $dt_08_30,
            'dt_10_00' => $dt_10_00,
            'dt_11_40' => $dt_11_40,
            'gruppo_1' => $gruppo_1,
            'gruppo_2' => $gruppo_2,
            'gruppo_3' => $gruppo_3,
            'gruppo_4' => $gruppo_4,
            'log_08_30' => $log_08_30,
            'log_10_00' => $log_10_00,
            'log_11_40' => $log_11_40,
            'gruppiEseguitiOggi' => $gruppiEseguitiOggi,
        );
    }
    
    /**
     * Test: getUltimoLogGruppo
     */
    public function testUltimoLogGruppoEsiste() {
        
        $baseData = $this->getBaseData();
        $dt_11_40 = $baseData['dt_11_40'];
        $gruppiEseguitiOggi = $baseData['gruppiEseguitiOggi'];
        
        $gruppoPrecedente = $this->service->getUltimoLogGruppo($gruppiEseguitiOggi);
        
        //Il gruppo estratto non deve essere NULL e deve essere l'ultimo gruppo in ordine di tempo
        $this->assertNotNull($gruppoPrecedente);
        $this->assertEquals($dt_11_40, $gruppoPrecedente->getCreatedAt());
    }
    
    /**
     * Test: getUltimoLogGruppo
     */
    public function testUltimoLogGruppoNonEsiste() {
        
        $gruppiEseguitiOggi = array();
        $gruppoPrecedente = $this->service->getUltimoLogGruppo($gruppiEseguitiOggi);
        
        //Il gruppo estratto DEVE essere NULL in quanto non ci sono gruppi eseguiti oggi
        $this->assertNull($gruppoPrecedente);
    }
    
    /**
     * Test: checkGruppoRispettaCondizioni
     */
    public function testGruppoRispettaCondizioni() {
        
        $baseData = $this->getBaseData();
        $gruppo_2 = $baseData['gruppo_2'];
        $gruppiEseguitiOggi = $baseData['gruppiEseguitiOggi'];
        
        /*
         * Il gruppo 2 può essere eseguito nuovamente perchè:
         * - la dipendenza è stata rispettata (il gruppo 1 è stato eseguito oggi)
         * - il numero massimo di iterazioni non è ancora stato raggiunto
         */
        
        $result = $this->service->checkGruppoRispettaCondizioni($gruppo_2, $gruppiEseguitiOggi);
        
        $this->assertEquals(true, $result);
    }
    
    /**
     * Test: checkGruppoRispettaCondizioni
     */
    public function testGruppoNonRispettaCondizioneDipendenza() {
        
        $baseData = $this->getBaseData();
        $gruppo_4 = $baseData['gruppo_4'];
        $gruppiEseguitiOggi = $baseData['gruppiEseguitiOggi'];
        
        //Il gruppo 4 NON può essere eseguito perchè il gruppo 3 non è mai stato eseguito oggi
        $result = $this->service->checkGruppoRispettaCondizioni($gruppo_4, $gruppiEseguitiOggi);
        
        $this->assertEquals(false, $result);
        
    }
    
    /**
     * Test: checkGruppoRispettaCondizioni
     */
    public function testGruppoNonRispettaCondizioneMaxIterazioni() {
        
        $baseData = $this->getBaseData();
        $gruppo_1 = $baseData['gruppo_1'];
        $gruppiEseguitiOggi = $baseData['gruppiEseguitiOggi'];
        
        //Il gruppo 1 NON può essere eseguito nuovamente perchè ha già raggiunto il numero massimo di iterazioni giornaliere
        $result = $this->service->checkGruppoRispettaCondizioni($gruppo_1, $gruppiEseguitiOggi);
        
        $this->assertEquals(false, $result);
    }
    
    /**
     * Test: getCandidatoFinaleGruppo
     */
    public function testGetCandidatoFinaleGruppo() {
        
        $baseData = $this->getBaseData();
        $gruppiEseguitiOggi = $baseData['gruppiEseguitiOggi'];
        
        //L'ultimo gruppo eseguito è il 2, quindi parto dal 3
        $listaCandidati = array($baseData['gruppo_3'], $baseData['gruppo_4']);
        
        $candidatoFinale = $this->service->getCandidatoFinaleGruppo($listaCandidati, $gruppiEseguitiOggi);
        
        //Il prossimo candidato deve essere il numero 3
        $this->assertNotNull($candidatoFinale);
        $this->assertEquals(3, $candidatoFinale->getId());
    }
}
