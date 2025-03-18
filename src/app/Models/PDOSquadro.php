<?php

namespace Game\Squadro\Models;

require_once './app/Models/JoueurSquadro.php';
require_once './app/Models/PartieSquadro.php';

use PDO;
use PDOStatement;

class PDOSquadro
{
    private static PDO $pdo;

    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        switch ($sgbd) {
            case 'mysql':
                self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
                break;
            case 'pgsql':
                self::$pdo = new PDO('pgsql:host=' . $host . ' dbname=' . $db . ' user=' . $user . ' password=' . $password);
                break;
            default:
                exit ("Type de sgbd non correct : $sgbd fourni, 'pgsql' attendu");
        }

        // pour récupérer aussi les exceptions provenant de PDOStatement
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* requêtes Préparées pour l'entitePlayerSquadro */
    private static PDOStatement $createPlayerSquadro;
    private static PDOStatement $selectPlayerByName;

    /******** Gestion des requêtes relatives à JoueurSquadro *************/
    public static function createPlayer(string $name): JoueurSquadro
    {
            $player_check = self::selectPlayerByName($name);
            if($player_check) return $player_check;
            // Prepare the statement to insert the player
            $stmt = self::$pdo->prepare("INSERT INTO JoueurSquadro (joueurNom) VALUES (:nom_joueur)");
            $stmt->bindParam(':nom_joueur', $name);
            $stmt->execute();

            // Get the ID of the inserted player
            $playerId = self::$pdo->lastInsertId();

            // Create and return the JoueurSquadro object
            $player = new JoueurSquadro();
            $player->setId((int) $playerId);
            $player->setNomJoueur($name);
            return $player;

    }

    public static function selectPlayerByName(string $name): ?JoueurSquadro
    {
        // Prepare the query to search for the player by name
        $stmt = self::$pdo->prepare("SELECT * FROM JoueurSquadro WHERE joueurNom = :nom_joueur LIMIT 1");
        $stmt->bindParam(':nom_joueur', $name);
        $stmt->execute();

        // If a player is found, return the player instance
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $player = new JoueurSquadro();
            $player->setId((int) $row['id']);
            $player->setNomJoueur($row['joueurNom']);
            return $player;
        }

        // If no player is found, return null
        return null;
    }

    public static function selectPlayerById(int $id): ?JoueurSquadro
    {
        // Prepare the query to search for the player by name
        $stmt = self::$pdo->prepare("SELECT * FROM JoueurSquadro WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // If a player is found, return the player instance
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $player = new JoueurSquadro();
            $player->setId((int) $row['id']);
            $player->setNomJoueur($row['joueurNom']);
            return $player;
        }

        // If no player is found, return null
        return null;
    }

    /* requêtes préparées pour l'entite PartieSquadro */
    private static PDOStatement $createPartieSquadro;
    private static PDOStatement $savePartieSquadro;
    private static PDOStatement $addPlayerToPartieSquadro;
    private static PDOStatement $selectPartieSquadroById;
    private static PDOStatement $selectAllPartieSquadro;
    private static PDOStatement $selectAllPartieSquadroByPlayerName;

    /******** Gestion des requêtes relatives à PartieSquadro *************/

    /**
     * initialisation et execution de $createPartieSquadro la requête préparée pour enregistrer une nouvelle partie
     */
    public static function createPartieSquadro(string $playerName, string $json): void
    {
        // Start a transaction to ensure data integrity
        self::$pdo->beginTransaction();

        try {
            $playerOne = self::selectPlayerByName($playerName)->getId();
            $gameStatus = 'waitingForPlayer';
            // Prepare the SQL statement to insert a new game
            $stmt = self::$pdo->prepare("INSERT INTO PartieSquadro (playerOne, gameStatus, json) VALUES (:playerOne, :gameStatus, :json)");
            $stmt->bindParam(':playerOne', $playerOne);
            $stmt->bindParam(':gameStatus', $gameStatus);
            $stmt->bindParam(':json', $json);
            $stmt->execute();


            // Commit the transaction
            self::$pdo->commit();
            } catch (Exception $e) {
                // If something goes wrong, roll back the transaction
                self::$pdo->rollBack();
                throw $e;
            }
    }

    /**
     * initialisation et execution de $savePartieSquadro la requête préparée pour changer
     * l'état de la partie et sa représentation json
     */
    public static function savePartieSquadro(string $gameStatus, string $json, int $partieId): void
    {
         // Start a transaction to ensure data integrity
         self::$pdo->beginTransaction();

         try {
             // Prepare the SQL statement to insert a new game
             $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET gameStatus = :gameStatus  ,json= :json WHERE partieId = :partieId");
             $stmt->bindParam(':gameStatus', $gameStatus);
             $stmt->bindParam(':json', $json);
             $stmt->bindParam(':partieId', $partieId);
             $stmt->execute();
 

             // Commit the transaction
             self::$pdo->commit();
             } catch (Exception $e) {
                 // If something goes wrong, roll back the transaction
                 self::$pdo->rollBack();
                 throw $e;
             }
    }

    /**
     * initialisation et execution de $addPlayerToPartieSquadro la requête préparée pour intégrer le second joueur
     */
    public static function addPlayerToPartieSquadro(string $playerName, string $json, int $gameId): void
    {   
        
        $player = self::createPlayer($playerName);
        // Start a transaction to ensure data integrity
        $playerId = $player->getId();
        self::$pdo->beginTransaction();

        try {
            // Prepare the SQL statement to insert a new game
            $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET playerTwo = :playerTwo  ,json= :json WHERE partieId = :partieId");
            $stmt->bindParam(':playerTwo', $playerId);
            $stmt->bindParam(':json', $json);
            $stmt->bindParam(':partieId', $gameId);
            $stmt->execute();

            // Commit the transaction
            self::$pdo->commit();
            } catch (Exception $e) {
                // If something goes wrong, roll back the transaction
                self::$pdo->rollBack();
                throw $e;
            }
    }

    /**
     * initialisation et execution de $selectPartieSquadroById la requête préparée pour récupérer
     * une instance de PartieSquadro en fonction de son identifiant
     */
    public static function getPartieSquadroById(int $gameId): ?PartieSquadro
    {
        // Prepare the query to search for the player by name
        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro WHERE partieId = :partieId LIMIT 1");
        $stmt->bindParam(':partieId', $gameId);
        $stmt->execute();

        // If a player is found, return the player instance
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $partie = PartieSquadro::fromJson($row['json']);
            return $partie;
        }

        // If no player is found, return null
        return null;
    }
    /**
     * initialisation et execution de $selectAllPartieSquadro la requête préparée pour récupérer toutes
     * les instances de PartieSquadro
     */
    public static function getAllPartieSquadro(): array
    {
        /** TODO **/
        $parties = [];

        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro");
        $stmt->execute();

       // Iterate through all the rows returned from the query
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Get the player details for playerOne
            $player = self::selectPlayerById($row['playerOne']);
            // Create a PartieSquadro instance and set its properties
            $partie = new PartieSquadro($player);
            $partie->setPartieId((int) $row['partieId']);
            
            // Add the PartieSquadro object to the $parties array
            $parties[] = $partie;
        }

        // Return the array of PartieSquadro objects
        return $parties;
    }

    /**
     * initialisation et execution de $selectAllPartieSquadroByPlayerName la requête préparée pour récupérer les instances
     * de PartieSquadro accessibles au joueur $playerName
     * ne pas oublier les parties "à un seul joueur"
     */
    public static function getAllPartieSquadroByPlayerName(string $playerName): array
    {

        $player = self::selectPlayerByName($playerName);   
        $playerId = $player->getId();
         /** TODO **/
         $parties = [];

        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro WHERE playerOne = :playerOne OR playerTwo = :playerTwo");
        $stmt->bindParam(':playerOne', $playerId);
        $stmt->bindParam(':playerTwo', $playerId);
        
        $stmt->execute();
 
        // Iterate through all the rows returned from the query
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
             // Get the player details for playerOne
             $player = self::selectPlayerById($row['playerOne']);
             // Create a PartieSquadro instance and set its properties
             $partie = new PartieSquadro($player, $row['gameStatus']);
             $partie->setPartieId((int) $row['partieId']);
             
             // Add the PartieSquadro object to the $parties array
             $parties[] = $partie;
         }
 
         // Return the array of PartieSquadro objects
         return $parties;
    }
    /**
     * initialisation et execution de la requête préparée pour récupérer
     * l'identifiant de la dernière partie ouverte par $playername
     */
    public static function getLastGameIdForPlayer(string $playerName): int
    {
        $player = self::selectPlayerByName($playerName);   
        $playerId = $player->getId();
         /** TODO **/
         $parties = [];

        $stmt = self::$pdo->prepare("SELECT MAX(Par) FROM PartieSquadro WHERE playerOne = :playerOne OR playerTwo = :playerTwo");
        $stmt->bindParam(':playerOne', $playerId);
        $stmt->bindParam(':playerTwo', $playerId);
        
        $stmt->execute();
 
        // Iterate through all the rows returned from the query
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
             // Get the player details for playerOne
             $player = self::selectPlayerById($row['playerOne']);
             // Create a PartieSquadro instance and set its properties
             $partie = new PartieSquadro($player);
             $partie->setPartieId((int) $row['partieId']);
             
             // Add the PartieSquadro object to the $parties array
             $parties[] = $partie;
         }
 
         // Return the array of PartieSquadro objects
         return $parties;
    }

}
