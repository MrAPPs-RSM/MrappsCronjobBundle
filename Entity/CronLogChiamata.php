<?php

namespace Mrapps\CronjobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrapps\BackendBundle\Entity\Base;

/**
 * CronLogChiamata
 *
 * @ORM\Table(name="mrapps_cronjob_log_chiamata")
 * @ORM\Entity(repositoryClass="Mrapps\CronjobBundle\Repository\CronLogChiamataRepository")
 */
class CronLogChiamata extends Base
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="CronLogGruppo", inversedBy="chiamate")
     * @ORM\JoinColumn(name="log_gruppo_id", referencedColumnName="id")
     */
    protected $logGruppo;

    /**
     * @ORM\ManyToOne(targetEntity="CronConfigChiamata", inversedBy="logs")
     * @ORM\JoinColumn(name="chiamata_id", referencedColumnName="id")
     */
    protected $chiamata;
    
    /**
     * @var int
     *
     * @ORM\Column(name="tentativo", type="integer")
     */
    protected $tentativo;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="log_data_inizio", type="datetime", nullable=true)
     */
    protected $logDataInizio;
    
    /**
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    protected $success;
    
    /**
     * @var string
     *
     * @ORM\Column(name="output", type="text", nullable=true)
     */
    protected $output;
    
    
    public function __construct() {
        parent::__construct();
        $this->setSuccess(null);
        $this->setLogDataInizio(new \DateTime());
        $this->setTentativo(1);
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
     * Set tentativo
     *
     * @param integer $tentativo
     *
     * @return CronLogChiamata
     */
    public function setTentativo($tentativo)
    {
        $this->tentativo = $tentativo;

        return $this;
    }

    /**
     * Get tentativo
     *
     * @return integer
     */
    public function getTentativo()
    {
        return $this->tentativo;
    }

    /**
     * Set logDataInizio
     *
     * @param \DateTime $logDataInizio
     *
     * @return CronLogChiamata
     */
    public function setLogDataInizio($logDataInizio)
    {
        $this->logDataInizio = $logDataInizio;

        return $this;
    }

    /**
     * Get logDataInizio
     *
     * @return \DateTime
     */
    public function getLogDataInizio()
    {
        return $this->logDataInizio;
    }

    /**
     * Set success
     *
     * @param boolean $success
     *
     * @return CronLogChiamata
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get success
     *
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set output
     *
     * @param string $output
     *
     * @return CronLogChiamata
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set chiamata
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamata
     *
     * @return CronLogChiamata
     */
    public function setChiamata(\Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamata = null)
    {
        $this->chiamata = $chiamata;

        return $this;
    }

    /**
     * Get chiamata
     *
     * @return \Mrapps\CronjobBundle\Entity\CronConfigChiamata
     */
    public function getChiamata()
    {
        return $this->chiamata;
    }

    /**
     * Set logGruppo
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogGruppo $logGruppo
     *
     * @return CronLogChiamata
     */
    public function setLogGruppo(\Mrapps\CronjobBundle\Entity\CronLogGruppo $logGruppo = null)
    {
        $this->logGruppo = $logGruppo;

        return $this;
    }

    /**
     * Get logGruppo
     *
     * @return \Mrapps\CronjobBundle\Entity\CronLogGruppo
     */
    public function getLogGruppo()
    {
        return $this->logGruppo;
    }
}
