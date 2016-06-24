<?php

namespace Mrapps\CronjobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrapps\BackendBundle\Entity\Base;

/**
 * CronConfigGruppo
 *
 * @ORM\Table(name="sml_cron_config_gruppo")
 * @ORM\Entity(repositoryClass="Mrapps\CronjobBundle\Repository\CronConfigGruppoRepository")
 */
class CronConfigGruppo extends Base
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
     * @ORM\ManyToOne(targetEntity="CronConfigGruppo", inversedBy="gruppiSubordinati")
     * @ORM\JoinColumn(name="gruppo_dipendente_id", referencedColumnName="id")
     */
    protected $gruppoDipendente;
    
    /**
     * @ORM\OneToMany(targetEntity="CronConfigGruppo", mappedBy="gruppoDipendente")
     */
    protected $gruppiSubordinati;
    
    /**
     * @ORM\OneToMany(targetEntity="CronConfigChiamata", mappedBy="gruppo", cascade={"remove"})
     */
    protected $chiamate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255)
     */
    protected $nome;
    
    /**
     * @ORM\OneToMany(targetEntity="CronLogGruppo", mappedBy="gruppo", cascade={"remove"})
     */
    protected $logs;
    
    /**
     * @ORM\Column(name="ora_min", type="time", nullable=true)
     */
    protected $oraMin;
    
    /**
     * @ORM\Column(name="ora_max", type="time", nullable=true)
     */
    protected $oraMax;
    
    /**
     * @var int
     *
     * @ORM\Column(name="max_iterazioni", type="integer", nullable=true)
     */
    protected $maxIterazioni;
    

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
     * Set gruppoDipendente
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppoDipendente
     *
     * @return CronConfigGruppo
     */
    public function setGruppoDipendente(\Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppoDipendente = null)
    {
        $this->gruppoDipendente = $gruppoDipendente;

        return $this;
    }

    /**
     * Get gruppoDipendente
     *
     * @return \Mrapps\CronjobBundle\Entity\CronConfigGruppo
     */
    public function getGruppoDipendente()
    {
        return $this->gruppoDipendente;
    }

    /**
     * Add gruppiSubordinati
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppiSubordinati
     *
     * @return CronConfigGruppo
     */
    public function addGruppiSubordinati(\Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppiSubordinati)
    {
        $this->gruppiSubordinati[] = $gruppiSubordinati;

        return $this;
    }

    /**
     * Remove gruppiSubordinati
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppiSubordinati
     */
    public function removeGruppiSubordinati(\Mrapps\CronjobBundle\Entity\CronConfigGruppo $gruppiSubordinati)
    {
        $this->gruppiSubordinati->removeElement($gruppiSubordinati);
    }

    /**
     * Get gruppiSubordinati
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGruppiSubordinati()
    {
        return $this->gruppiSubordinati;
    }

    /**
     * Add chiamate
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamate
     *
     * @return CronConfigGruppo
     */
    public function addChiamate(\Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamate)
    {
        $this->chiamate[] = $chiamate;

        return $this;
    }

    /**
     * Remove chiamate
     *
     * @param \Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamate
     */
    public function removeChiamate(\Mrapps\CronjobBundle\Entity\CronConfigChiamata $chiamate)
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

    /**
     * Set nome
     *
     * @param string $nome
     *
     * @return CronConfigGruppo
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Add log
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogGruppo $log
     *
     * @return CronConfigGruppo
     */
    public function addLog(\Mrapps\CronjobBundle\Entity\CronLogGruppo $log)
    {
        $this->logs[] = $log;
        return $this;
    }
    
    /*
     * Set oraMin
     *
     * @param \DateTime $oraMin
     *
     * @return CronConfigGruppo
     */
    public function setOraMin($oraMin)
    {
        $this->oraMin = $oraMin;
    
        return $this;
    }

    /**
     * Remove log
     *
     * @param \Mrapps\CronjobBundle\Entity\CronLogGruppo $log
     */
    public function removeLog(\Mrapps\CronjobBundle\Entity\CronLogGruppo $log)
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

    /*
     * Get oraMin
     *
     * @return \DateTime
     */
    public function getOraMin()
    {
        return $this->oraMin;
    }

    /**
     * Set oraMax
     *
     * @param \DateTime $oraMax
     *
     * @return CronConfigGruppo
     */
    public function setOraMax($oraMax)
    {
        $this->oraMax = $oraMax;

        return $this;
    }

    /**
     * Get oraMax
     *
     * @return \DateTime
     */
    public function getOraMax()
    {
        return $this->oraMax;
    }

    /**
     * Set maxIterazioni
     *
     * @param integer $maxIterazioni
     *
     * @return CronConfigGruppo
     */
    public function setMaxIterazioni($maxIterazioni)
    {
        $this->maxIterazioni = $maxIterazioni;

        return $this;
    }

    /**
     * Get maxIterazioni
     *
     * @return integer
     */
    public function getMaxIterazioni()
    {
        return $this->maxIterazioni;
    }
}
