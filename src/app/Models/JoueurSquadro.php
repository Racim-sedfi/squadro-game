<?php

namespace Game\Squadro\Models;

class JoueurSquadro {
    /**
     * Summary of nomJoueur
     * @var string
     */
    private string $nomJoueur;
    /**
     * Summary of id
     * @var int
     */
    private int $id;


    /**
     * Summary of getNomJoueur
     * @return void
     */
    public function getNomJoueur(): string{
        return $this->nomJoueur;
    }

    /**
     * Summary of setNomJoueur
     * @param string $nom
     * @return void
     */
    public function setNomJoueur(string $nom): void{
        $this->nomJoueur = $nom;
    }

    /**
     * Summary of getId
     * @return void
     */
    public function getId(): int{
        return $this->id;
    }

    /**
     * Summary of setId
     * @param int $id
     * @return void
     */
    public function setId(int $id): void{
        $this->id = $id;
    }

    /**
     * Summary of toJson
     * @return void
     */
    public function toJson(): string{
        return json_encode($this);
    }

    /**
     * Summary of fromJson
     * @param string $json
     * @return void
     */
    public function fromJson(string $json): JoueurSquadro{
        return json_decode($json);
    }
    
}