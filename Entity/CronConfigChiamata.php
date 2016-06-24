<?php

namespace Mrapps\CronjobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrapps\BackendBundle\Entity\Base;

/**
 * CronConfigChiamata
 *
 * @ORM\Table(name="sml_cron_config_chiamata")
 * @ORM\Entity(repositoryClass="Mrapps\CronjobBundle\Repository\CronConfigChiamataRepository")
 */
class CronConfigChiamata extends Base
{
    const TIPI = array('CLASSE', 'URL');
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CronConfigGruppo", inversedBy="chiamate")
     * @ORM\JoinColumn(name="gruppo_id", referencedColumnName="id")
     */
    protected $gruppo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="tipo_chiamata", type="string", length=255)
     */
    protected $tipoChiamata;
    
    /**
     * @var string
     *
     * @ORM\Column(name="endpoint", type="string", length=1000)
     */
    protected $endpoint;
    
    /**
     * @var string
     *
     * @ORM\Column(name="parametri", type="string", length=1000)
     */
    protected $parametri;
    
    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=1000)
     */
    protected $descrizione;
    
    /**
     * @var int
     *
     * @ORM\Column(name="max_tentativi", type="integer")
     */
    protected $maxTentativi;
    
    /**
     * @ORM\OneToMany(targetEntity="CronLogChiamata", mappedBy="chiamata")
     */
    protected $logs;
    
    
    public function __construct() {
        parent::__construct();
        $this->setMaxTentativi(1);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    
    

    /**
     * Set maxTentativi
     *
     * @param integer $maxTentativi
     *
     * @return CronConfigChiamata
     */
    public function setMaxTentativi($maxTentativi)
    {
        $this->maxTentativi = $maxTentativi;

        return $this;
    }

    /**
     * Get maxTentativi
     *
     * @return integer
     */
    public function getMaxTentativi()
    {
        return $this->maxTentativi;
    }

    /**
     * Set gruppo
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppo
     *
     * @return CronConfigChiamata
     */
    public function setGruppo(\Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppo = null)
    {
        $this->gruppo = $gruppo;

        return $this;
    }

    /**
     * Get gruppo
     *
     * @return \Mrapps\CronjobBundle\Entity\CronConfigGruppo
     */
    public function getGruppo()
    {
        return $this->gruppo;
    }


    /**
     * Set descrizione
     *
     * @param string $descrizione
     *
     * @return CronConfigChiamata
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * Get descrizione
     *
     * @return string
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * Set parametri
     *
     * @param string $parametri
     *
     * @return CronConfigChiamata
     */
    public function setParametri($parametri)
    {
        $this->parametri = $parametri;

        return $this;
    }

    /**
     * Get parametri
     *
     * @return string
     */
    public function getParametri()
    {
        return $this->parametri;
    }

    /**
     * Add log
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogChiamata $log
     *
     * @return CronConfigChiamata
     */
    public function addLog(\Mrapps\CronjobBundle\Entity\CronLogChiamata $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogChiamata $log
     */
    public function removeLog(\Mrapps\CronjobBundle\Entity\CronLogChiamata $log)
    {
        $this->logs->removeElement($log);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set endpoint
     *
     * @param string $endpoint
     *
     * @return CronConfigChiamata
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set tipoChiamata
     *
     * @param string $tipoChiamata
     *
     * @return CronConfigChiamata
     */
    public function setTipoChiamata($tipoChiamata)
    {
        $this->tipoChiamata = $tipoChiamata;

        return $this;
    }

    /**
     * Get tipoChiamata
     *
     * @return string
     */
    public function getTipoChiamata()
    {
        return $this->tipoChiamata;
    }
}
