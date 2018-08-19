<?php
namespace Fludixx\BedWars;

use pocketmine\item\Item;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\math\Vector3;
use pocketmine\Server;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\tile\Chest;
use pocketmine\tile\Hopper;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;
use pocketmine\level\Position;

class SpawnTask extends Task
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
			"bronze" => Item::get(Item::BRICK),
		);

		foreach ($tiles as $tile) {
			if ($tile instanceof Sign) {
				$spawn = $tile->getLine(0);
				foreach ($spawnable as $label => $item) {
					if ($label == $spawn) {
						if ($label == $spawn) {
							$pos = new Position($tile->getX()+0.5, $tile->getY() + 2, $tile->getZ()+0.5, $tile->getLevel
							());
							$players = $this->plugin->getServer()->getOnlinePlayers();
							foreach($players as $player) {
								if($player->distance($pos) <= 6) {
									$map->dropItem($pos, $item, new Vector3(0, 0.1, 0));
								}
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
}