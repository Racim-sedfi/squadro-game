<?php

namespace Game\Squadro\Models;
use ArrayAccess;
use Countable;

class ArrayPieceSquadro implements ArrayAccess, Countable {

    private array $pieces = [];

    /**
     * Ajouter une piece
     * @param PieceSquadro $piece
     * @return void
     */
    public function add(PieceSquadro $piece): void{
        $this->pieces[] = $piece;
    }

    public function remove(int $index): void{
        unset($this->pieces[$index]);
    }


    /**
     * Summary of offsetSet
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->pieces[] = $value;
        } else {
            $this->pieces[$offset] = $value;
        }
    }

    /**
     * Summary of offsetExists
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
        return isset($this->pieces[$offset]);
    }

    /**
     * Summary of offsetUnset
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void {
        #Already done in remove
    }

    /**
     * Summary of offsetGet
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed {
        return isset($this->pieces[$offset]) ? $this->pieces[$offset] : null;
    }

    /**
     * Summary of count
     * @return int
     */
    public function count(): int{
        return count($this->pieces);
    }

}