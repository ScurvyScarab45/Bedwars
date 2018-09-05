<?php

/**
 * @author Fludixx
 * @copyright 2018 Fludixx
 * @version 0.3
 * @license MIT
 *
 */

namespace Fludixx\BedWars;

use pocketmine\item\Bed;
use pocketmine\math\Vector3;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;
use pocketmine\level\Position;

class SignReloader extends Task
{
	public $plugin;
	public $level;

	public function __construct(Bedwars $plugin, Level $level)
	{

		/**
		 * @param Bedwars $plugin
		 * @param Level $level
		 */

		$this->plugin = $plugin;
		$this->level = $level;
	}

	public function onRun(int $tick)
	{
		$levelname = $this->level->getName();
		$this->plugin->getLogger()->info(Bedwars::PREFIX . "Reloade Signs auf: $levelname...");
		$tiles = $this->plugin->getServer()->getDefaultLevel()->getTiles();
		foreach ($tiles as $tile) {
			if ($tile instanceof \pocketmine\tile\Sign) {
				$text = $tile->getText();
				if ($text[0] == Bedwars::NAME || $text[0] == f::RED . "Bedwars") {
					$this->plugin->getScheduler()->scheduleRepeatingTask(new BwSignUpdater($this->plugin, $tile), 20);
					$this->plugin->getLogger()->info("Schild wurde Reloaded!");
				}
			}
		}

	}
}