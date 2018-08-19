<?php
namespace Fludixx\BedWars;

use pocketmine\item\Item;
use pocketmine\Server;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;
use pocketmine\level\Position;
use pocketmine\tile\Chest;
use pocketmine\tile\Hopper;

class SpawnIronTask extends Task
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
		$map = $this->level;
		$bw = $this->plugin;
		$tiles = $map->getTiles();
		$levelc = new Config("/cloud/bw/" . $map->getFolderName() . ".yml", 2);
		if($levelc->get("busy") == false) {
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
		$spawnable = array(
			"iron" => Item::get(Item::IRON_INGOT)
		);

		foreach ($tiles as $tile) {
			if ($tile instanceof Sign) {
				$spawn = $tile->getLine(0);
				foreach ($spawnable as $label => $item) {
					if($label == $spawn) {
						if($label == $spawn) {
							$pos = new Position($tile->getX(), $tile->getY() + 2, $tile->getZ(), $tile->getLevel());
							$map->dropItem($pos, $item);
						}
					}
				}
			}
		}
		$players = $bw->getServer()->getOnlinePlayers();
		$counter = 0;
		foreach ($players as $player) {

			if ($player->getLevel()->getFolderName() == $map->getFolderName()) {
				$counter++;
			}
		}
		if ($counter <= 1) {
			$bw->getScheduler()->cancelTask($this->getTaskId());
		}


	}
}