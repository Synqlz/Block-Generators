<?php

declare(strict_types=1);

namespace Kirkham05\BlockGens;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{

    /**
     * @param BlockPlaceEvent $event
     * @ignoreCancelled true
     * @priority HIGH
     */
    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();
        $blockB = $event->getBlockAgainst();

        $genBlock = Main::getInstance()->getGenDataFor($block->getId(), $block->getDamage());

        if ($genBlock !== null) {
            $tile = $blockB->getLevel()->getTile($blockB);
            if ($tile instanceof GenTile && ($block->getId() === $blockB->getId() && $block->getDamage() === $blockB->getDamage())) {
                $event->setCancelled();
                if ($tile->getStackAmount() < Main::getInstance()->getMaxStackAmount()) {
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $tile->increaseStackSize();
                    $player->sendMessage("Generator stack: " . $tile->getStackAmount() . "/" . Main::getInstance()->getMaxStackAmount());
                } else $player->sendMessage(TextFormat::RED . "This generator is already " . $tile->getStackAmount() . "/" . Main::getInstance()->getMaxStackAmount());
            } else {
                $nbt = new CompoundTag("", [
                    new StringTag("id", GenTile::GEN_TILE),
                    new IntTag("x", $block->x),
                    new IntTag("y", $block->y),
                    new IntTag("z", $block->z)
                ]);
                Tile::createTile(GenTile::GEN_TILE, $block->getLevel(), $nbt);
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @ignoreCancelled true
     * @priority HIGH
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $tile = $block->getLevel()->getTile($block);
        if ($tile instanceof GenTile) {
            $event->setDrops([Item::get($block->getId(), $block->getDamage(), $tile->getStackAmount())]);
            $block->getLevel()->removeTile($tile);
        }
    }

}