<?php

namespace App\Entity;

use App\Repository\FindSortieRepository;
use Symfony\Component\Validator\Constraints as Assert;


class FindSortie
{
    /**
     * @Assert\Type(type="string")
     */
    private $nomSortie;

    /**
     * @Assert\Type(type="string")
     */
    private $nomCampus;

    /**
     * @Assert\Date()
     */
    private $dateDebut;

    /**
     * @Assert\Date()
     */
    private $dateFin;

    /**
     * @Assert\Type(type="boolean")
     * @var boolean|null
     */
    private $mesSorties;

    /**
     * @Assert\Type(type="boolean")
     * @var boolean|null
     */
    private $mesInscriptions;

    /**
     * @Assert\Type(type="boolean")
     * @var boolean|null
     */
    private $pasEncoreInscrit;

    /**
     * @Assert\Type(type="boolean")
     * @var boolean|null
     */
    private $sortiesPassees;

    /**
     * @return mixed
     */
    public function getNomSortie()
    {
        return $this->nomSortie;
    }

    /**
     * @param mixed $nomSortie
     */
    public function setNomSortie($nomSortie): void
    {
        $this->nomSortie = $nomSortie;
    }

    /**
     * @return mixed
     */
    public function getNomCampus()
    {
        return $this->nomCampus;
    }

    /**
     * @param mixed $nomCampus
     */
    public function setNomCampus($nomCampus): void
    {
        $this->nomCampus = $nomCampus;
    }

    /**
     * @return mixed
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * @param mixed $dateDebut
     */
    public function setDateDebut($dateDebut): void
    {
        $this->dateDebut = $dateDebut;
    }

    /**
     * @return mixed
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * @param mixed $dateFin
     */
    public function setDateFin($dateFin): void
    {
        $this->dateFin = $dateFin;
    }


    /**
     * @return bool|null
     */
    public function getMesSorties(): ?bool
    {
        return $this->mesSorties;
    }

    /**
     * @param bool|null $mesSorties
     */
    public function setMesSorties(?bool $mesSorties): void
    {
        $this->mesSorties = $mesSorties;
    }

    /**
     * @return bool|null
     */
    public function getMesInscriptions(): ?bool
    {
        return $this->mesInscriptions;
    }

    /**
     * @param bool|null $mesInscriptions
     */
    public function setMesInscriptions(?bool $mesInscriptions): void
    {
        $this->mesInscriptions = $mesInscriptions;
    }

    /**
     * @return bool|null
     */
    public function getPasEncoreInscrit(): ?bool
    {
        return $this->pasEncoreInscrit;
    }

    /**
     * @param bool|null $pasEncoreInscrit
     */
    public function setPasEncoreInscrit(?bool $pasEncoreInscrit): void
    {
        $this->pasEncoreInscrit = $pasEncoreInscrit;
    }

    /**
     * @return bool|null
     */
    public function getSortiesPassees(): ?bool
    {
        return $this->sortiesPassees;
    }

    /**
     * @param bool|null $sortiesPassees
     */
    public function setSortiesPassees(?bool $sortiesPassees): void
    {
        $this->sortiesPassees = $sortiesPassees;
    }


}
