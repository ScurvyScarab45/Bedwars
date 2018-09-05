<?php

/**
 * @author Fludixx
 * @copyright 2018 Fludixx
 * @version 0.3
 * @license MIT
 *
 */

namespace Fludixx\BedWars;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
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
	public $spawn_particles = array();

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
		$spawnable = array(
			"bronze" => Item::get(Item::BRICK)
		);

		foreach ($tiles as $tile) {
			if ($tile instanceof Sign) {
				$spawn = $tile->getLine(0);
				foreach ($spawnable as $label => $item) {
					if ($label == $spawn) {
						if ($label == $spawn) {
							$pos = new Position($tile->getX()+0.5, $tile->getY() + 2, $tile->getZ()+0.5, $tile->getLevel());
							$identifier = (string)(round($tile->getX())+0.5)."-".
								(string)(round($tile->getY())+2.5)."-".
								(string)(round($tile->getZ())+0.5);
							if($levelc->get("$identifier") == false) {
								$instance = new FloatingTextParticle(new Vector3($tile->getX() + 0.5,
									$tile->getY() + 2.5,
									$tile->getZ() + 0.5), "", f::GOLD . "0");
								$levelc->set("$identifier", array("bronze" => 0, "instance" => serialize($instance)));
								$levelc->save();
								$this->level->dropItem($pos, $item, new Vector3(0, 0.0, 0));
							}
							if($levelc->get($identifier)["bronze"] == 0) {
								$spawner = $levelc->get("$identifier");
								$textParticle = unserialize($spawner["instance"]);
								$textParticle->setInvisible(true);
								$this->level->addParticle($textParticle);
							}
								if(array_search($identifier, $this->spawn_particles) == false) {
									$this->spawn_particles[] = $identifier;
								}
								$spawner = $levelc->get("$identifier");
								$currentBronze = (int) $spawner["bronze"];
								$textParticle = unserialize($spawner["instance"]);
								$iteme = $this->level->getNearestEntity($pos, 3);
								if($iteme instanceof Player) {
									$this->level->dropItem($pos, $item, new Vector3(0, 0.0, 0));
								}
								if($currentBronze >= 120) {

								} else {
									$spawner = $levelc->get("$identifier");
									$spawner["bronze"] = $currentBronze+1;
									$levelc->set("$identifier", $spawner);
									$levelc->save();
									$textParticle->setInvisible(true);
									$this->level->addParticle($textParticle);
									$textParticle->setInvisible(false);
									$textParticle->setTitle(f::GOLD.$spawner["bronze"]);
									$this->level->addParticle($textParticle);
									$spawner = $levelc->get("$identifier");
									$spawner["instance"] = serialize($textParticle);
									$levelc->set("$identifier", $spawner);
									$levelc->save();
								}
							}
						}
					}
				}
			}
			if($levelc->get("busy") == false) {
				$levelc->set("spawnpoints", $this->spawn_particles);
				$levelc->save();
				foreach($this->spawn_particles as $particle) {
					$identifier = $levelc->get($particle);
					$obj = unserialize($identifier["instance"]);
					$obj->setInvisible(true);
					$this->level->addParticle($obj);
				}
			}
	}
}