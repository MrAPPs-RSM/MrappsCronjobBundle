<?php

namespace Mrapps\CronjobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrapps\BackendBundle\Entity\Base;

/**
 * CronLogGruppo
 *
 * @ORM\Table(name="sml_cron_log_gruppo")
 * @ORM\Entity(repositoryClass="Mrapps\CronjobBundle\Repository\CronLogGruppoRepository")
 */
class CronLogGruppo extends Base
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
     * @ORM\Column(name="completato", type="boolean")
     */
    protected $completato = false;
    
    /**
     * @var int
     *
     * @ORM\Column(name="iterazione", type="integer")
     */
    protected $iterazione;
    
    /**
     * @ORM\ManyToOne(targetEntity="CronConfigGruppo", inversedBy="logs")
     * @ORM\JoinColumn(name="gruppo_id", referencedColumnName="id")
     */
    protected $gruppo;
    
    /**
     * @ORM\OneToMany(targetEntity="CronLogChiamata", mappedBy="logGruppo", cascade={"remove"})
     */
    protected $chiamate;
    
    
    public function __construct() {
        parent::__construct();
        $this->setCompletato(false);
        $this->setIterazione(1);
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
     * Set completato
     *
     * @param boolean $completato
     *
     * @return CronLogGruppo
     */
    public function setCompletato($completato)
    {
        $this->completato = $completato;

        return $this;
    }

    /**
     * Get completato
     *
     * @return boolean
     */
    public function getCompletato()
    {
        return $this->completato;
    }

    /**
     * Set iterazione
     *
     * @param integer $iterazione
     *
     * @return CronLogGruppo
     */
    public function setIterazione($iterazione)
    {
        $this->iterazione = $iterazione;

        return $this;
    }

    /**
     * Get iterazione
     *
     * @return integer
     */
    public function getIterazione()
    {
        return $this->iterazione;
    }

    /**
     * Set gruppo
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppo
     *
     * @return CronLogGruppo
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
     * Add chiamate
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogChiamata $chiamate
     *
     * @return CronLogGruppo
     */
    public function addChiamate(\Mrapps\CronjobBundle\Entity\CronLogChiamata $chiamate)
    {
        $this->chiamate[] = $chiamate;

        return $this;
    }

    /**
     * Remove chiamate
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogChiamata $chiamate
     */
    public function removeChiamate(\Mrapps\CronjobBundle\Entity\CronLogChiamata $chiamate)
    {
        $this->chiamate->removeElement($chiamate);
    }

    /**
     * Get chiamate
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChiamate()
    {
        return $this->chiamate;
    }
}
