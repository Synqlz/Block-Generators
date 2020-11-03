<?php

declare(strict_types=1);

namespace Kirkham05\BlockGens;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{

    /** @var Config */
    private $config;

    private static $instance;

    public function onEnable()
    {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        self::$instance = $this;

        Tile::registerTile(GenTile::class, [GenTile::GEN_TILE]);
    }

    public function getGenDataFor(int $id, int $meta): ?array
    {
        foreach ($this->getConfig()->get("generators", []) as $gen) {
            $gen = explode(":", $gen);

            if ($id === (int) $gen[0] && $meta === (int) $gen[1]) {
                return $gen;
            }
        }
        return null;
    }

    public function getMaxStackAmount(): int
    {
        return $this->getConfig()->get("max_stack", 10);
    }

    public function getGenRateDecrease(): int
    {
        return $this->getConfig()->get("gen_rate_decrease", 1);
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }


    public function getConfig(): Config
    {
        return $this->config;
    }
}