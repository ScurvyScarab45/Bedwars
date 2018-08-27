<?php

namespace Fludixx\BedWars;

use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\Server;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;
use pocketmine\level\Position;

class BwCountdown extends Task
{
	public $plugin;
	public $level;
	public $min;

	public function __construct(Bedwars $plugin, Level $level, int $min)
	{

		/**
		 * @param Bedwars $plugin
		 * @param Level $level
		 */

		$this->plugin = $plugin;
		$this->level = $level;
		$this->min = $min;
	}

	public function onRun(int $tick)
	{
		$name = $this->level->getFolderName();
		$c = new Config("/cloud/bw/$name.yml", Config::YAML);
		$cd = (int)$c->get("countdown");
		$cd = $cd - 1;
		$c->set("countdown", $cd);
		$c->save();
		$time = $c->get("countdown");
		$players = $this->plugin->getServer()->getOnlinePlayers();
		$counter = 0;
		foreach ($players as $player) {
			if ($player->getLevel()->getFolderName() == $name) {
				$counter++;
				$player->setXpLevel((int)$time);
				$xpbar = (double)bcmul((string)bcdiv((string)1, (string)60, 6), (string)$time, 6);
				$player->setXpProgress($xpbar);
			}
		}
		if ($time == 30) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() == $name) {
					$player->sendMessage($this->plugin->prefix . "Noch 30 Sekunden!");
				}
			}
		}
		if ($time == 10) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() == $name) {
					$player->sendMessage($this->plugin->prefix . "Noch 10 Sekunden!");
				}
			}
		}
		if ($time == 5) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() == $name) {
					$player->sendMessage($this->plugin->prefix . "Noch 5 Sekunden!");
				}
			}
		}
		if ($counter < $this->min) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() == $name) {
					$player->sendMessage($this->plugin->prefix . "Countdown wurde unterbrochen! Zuwenige Spieler.");
					$this->plugin->getScheduler()->cancelTask($this->getTaskId());
				}
			}
		}
		if ($time == 1) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			$teamint = 1;
			$teamdurchlauf = 0;
			$gold = 0;
			$nogold = 0;
			foreach ($players as $player) {
				if ($player->getLevel()->getFolderName() == $name) {
					$dimension = (string)$c->get("dimension");
					$playerProTeam = (int)substr($dimension, -1);
					$allTeams = $dimension[0];
					$cp = new Config("/cloud/users/".$player->getName().".yml", 2);
					$pname = $player->getName();
					if($cp->get("team") != false) {
						$cp->set("pos", $cp->get("team"));
						$cp->set("bett", true);
						$cp->save();
					}
					if($cp->get("gold_vote") == true) {
						$gold++;
					} else {
						$nogold++;
					}
					$player->sendMessage(f::BOLD . f::GREEN . "Das Spiel beginnt!");
					$pos = $cp->get("pos");
					$spawn = $c->get("p$pos");
					$cp->set("bwplays", (int)$cp->get("bwplays")+1);$cp->save();
					$pos = new Position($spawn[0], $spawn[1], $spawn[2], $this->level);
					$player->teleport($pos);
					$player->setGamemode(0);
					$this->plugin->getEq($player);
					$this->plugin->getScheduler()->scheduleRepeatingTask(new BwAsker($this->plugin, $player), 10);
					$this->plugin->getLogger()->info("Asker Task hat den Wert '$pname' bekommen.");
				}

			}
			$c->set("busy", true);
			$c->save();
			$items = $this->level->getEntities();
			foreach($items as $item) {
				if($item instanceof ItemEntity || $item instanceof Item) {
					$item->despawnFromAll();
					$item->kill();
				}
			}
			$this->plugin->getScheduler()->scheduleRepeatingTask(new SpawnTask($this->plugin, $this->level), 15);
			$this->plugin->getScheduler()->scheduleRepeatingTask(new SpawnIronTask($this->plugin, $this->level), 20 * 30);
			if($nogold > $gold) {
				$this->plugin->sagiri->sendLevelBrodcast($this->plugin::PREFIX."Gold-Vote Ergebniss: ".f::GREEN."GOLD AN", $this->level, false);
				$this->plugin->getScheduler()->scheduleRepeatingTask(new SpawnGoldTask($this->plugin, $this->level), 20 * 60);
			} else {
				$this->plugin->sagiri->sendLevelBrodcast($this->plugin::PREFIX."Gold-Vote Ergebniss: ".f::RED."GOLD AUS", $this->level, false);
			}
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());

		}

	}
}
