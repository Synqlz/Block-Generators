<?php

declare(strict_types=1);

namespace Kirkham05\BlockGens;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\tile\Tile;

class GenTile extends Tile
{
    /** @var int */
    public $id;

    const GEN_TILE = "gen_tile";

    const STACK_COUNT = "stack_count";

    /** @var int */
    private $stackCount = 1;

    /** @var int */
    private $genRate = 20;

    /** @var int */
    private $lastGen = 0;

    public function onUpdate(): bool
    {
        $block = $this->getLevel()->getBlock($this);
        $blockAbove = $this->getLevel()->getBlock($this->add(0, 1));
        if (!$block instanceof Air) {
            if($blockAbove instanceof Air) {
                $this->lastGen++;
                $decreaseBy = $this->stackCount * Main::getInstance()->getGenRateDecrease();
                if ($this->lastGen >= $this->genRate - $decreaseBy) {
                    $genData = Main::getInstance()->getGenDataFor($block->getId(), $block->getDamage());
                    if ($genData !== null) {
                        $this->getLevel()->setBlock($this->add(0, 1, 0), Block::get((int)$genData[2] ?? 0, (int)$genData[3] ?? 0));
                        $this->lastGen = 0;
                    }
                }
            }
        } else $this->getLevel()->removeTile($this);
        return true;
    }

    public function getStackAmount(): int
    {
        return $this->stackCount;
    }

    public function increaseStackSize(int $amount = 1): void
    {
        $this->stackCount += $amount;
    }

    public function decreaseStackSize(int $amount = 1): void
    {
        $this->stackCount -= $amount;
    }

    protected function readSaveData(CompoundTag $nbt): void
    {
        $this->genRate = Main::getInstance()->getConfig()->get("gen_rate", 20);
        if($nbt->hasTag(self::STACK_COUNT, IntTag::class)) {
            $this->stackCount = $nbt->getInt(self::STACK_COUNT);
        }
        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $nbt->setInt(self::STACK_COUNT, $this->stackCount);
    }
}