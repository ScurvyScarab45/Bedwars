<?php

/**
 * @author Fludixx
 * @copyright 2018 Fludixx
 * @version 0.3
 * @license MIT
 *
 */

namespace Fludixx\BedWars;

use pocketmine\Server;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;
use pocketmine\Player;

class BwAsker extends Task
{
	public $plugin;
	public $player;

	public function __construct(Bedwars $plugin, Player $player)
	{
		/**
		 * @param Bedwars $plugin
		 * @param Player $player
		 */

		$this->plugin = $plugin;
		$this->player = $player;
	}

	public function onRun(int $tick)
	{
		$player = $this->player;
		$name = $player->getName();
		$c = new Config("/cloud/users/$name.yml", Config::YAML);
		$pos = (int)$c->get("pos");
		$wool = (bool)$c->get("bett");
		$height = $player->getY();
		$arena = $player->getLevel();
		$arenaname = $arena->getFolderName();
		$ca = new Config("/cloud/bw/$arenaname.yml");
		if(!$player->isOnline()) {
			$this->plugin->getLogger()->info("Task für $name beendet!");
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
		if($pos == false) {
			$player->sendMessage(f::BOLD.f::RED."Du bist Gestorben!");
			$lobby = $this->plugin->getServer()->getDefaultLevel();
			$pos = new Position($lobby->getSafeSpawn()->getX(), $lobby->getSafeSpawn()->getY(),
				$lobby->getSafeSpawn()->getZ(), $lobby);
			$player->setSpawn($pos);
			$player->teleport($pos);
			$player->getInventory()->clearAll();
			$players = $this->plugin->getServer()->getOnlinePlayers();
			$counter = 0;
			foreach($players as $person) {
				if($person->getLevel()->getFolderName() == $arenaname) {
					$counter++;
					$pename = $person->getName();
					$pc = new Config("/cloud/users/$pename.yml", Config::YAML);
					$pos = $pc->get("pos");
					$pc->set("bw", false);
					$pc->save();
					$person->sendMessage($this->plugin->prefix."$name ist Ausgeschieden!");
				}
			}
			foreach($players as $person) {
				if ($person->getLevel()->getFolderName() == $arenaname) {
					$person->sendMessage($this->plugin->prefix."Es sind noch $counter Spieler übrig!");
				}
			}
			$this->plugin->getLogger()->info("$name");
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}

		$players = $this->plugin->getServer()->getOnlinePlayers();
		$counter = 0;
		foreach($players as $person) {
			if($person->getLevel()->getFolderName() == $arenaname) {
				$counter++;
			}
		}

		// SCOREBOARD
		$woolmsg = $wool;
		$blank = "                                 ";
		if($wool == true) {
			$woolmsg = f::BOLD.f::GREEN."O".f::RESET;
		} else {
			$woolmsg = f::BOLD.f::RED."X".f::RESET;
		}
		$players= $this->plugin->getServer()->getOnlinePlayers();
		$p1 = null;
		$p2 = null;
		$p3 = null;
		$p4 = null;
		$p5 = null;
		$p6 = null;
		$p7 = null;
		$p8 = null;

		foreach($players as $p) {
			if($p->getLevel()->getFolderName() == $player->getLevel()->getFolderName()) {
				$cp = new Config("/cloud/users/".$p->getName().".yml", 2);
				$var = "p".(int)$cp->get("pos");
				if($cp->get("bett") != false) {
					$$var = $this->plugin->ColorInt2Color($this->plugin->teamIntToColorInt((int)$cp->get("pos")))." ";
				} else {
					$$var = "";
				}
			}
		}

		$player->addActionBarMessage(
			f::RESET.f::WHITE."$blank $blank Team: ".f::WHITE .$this->plugin->ColorInt2Color
			($this->plugin->teamIntToColorInt((int)
			$pos))."\n"
			.f::WHITE ."$blank $blank Spieler: ".f::GOLD.$counter."\n"
			.f::WHITE."$blank $blank Bett: ".f::WHITE.$woolmsg."\n"
			.f::WHITE."$blank $blank Betten: $p1$p2$p3$p4$p5$p6$p7$p8"
			."\n\n\n\n\n\n\n\n\n\n");


		$players = $this->plugin->getServer()->getOnlinePlayers();
		$otherplayers = false;
		foreach($players as $person) {
			if($person->getLevel()->getFolderName() == $player->getLevel()->getFolderName() && $person->getGamemode() ==  0) {
				$pname = $person->getName();
				$cp = new Config("/cloud/users/$pname.yml", 2);
				if ($cp->get("pos") != $c->get("pos")) {
					$otherplayers = true;
				}
			}
		}
		if($otherplayers == false) {
			$players = $this->plugin->getServer()->getOnlinePlayers();
			foreach($players as $person) {
				if($person->getLevel()->getFolderName() == $player->getLevel()->getFolderName()) {
					if($person->getGamemode() == 3) {
						$person->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
					}
				}
			}
			$c->set("pos", false);
			$c->set("bw", false);
			$c->save();
			$player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->getInventory()->clearAll();
			$player->setXpLevel(0);
			$arena->unload();
			$this->plugin->getServer()->loadLevel($arenaname);
			$this->plugin->getServer()->getLevelByName($arenaname)->setAutoSave(false);
			$ca->set("busy", false);
			$ca->set("players", 0);
			$ca->set("countdown", 60);
			$ca->save();
			$ca->set("restart", true);
			$ca->save();
			$this->plugin->getScheduler()->scheduleDelayedTask(new SignReloader($this->plugin, $player->getLevel()), 40);
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
		if($height < 1) {
			if($wool == true) {
				$spawn = (array)$ca->get("p$pos");
				$pos = new Position($spawn['0'], $spawn['1'], $spawn['2']);
				$player->teleport($pos);
				$this->plugin->getEq($player);
				$player->getArmorInventory()->clearAll();
				$player->setHealth(20);
				$player->setFood(20);
				$players = $this->plugin->getServer()->getOnlinePlayers();
				foreach($players as $person) {
					if ($person->getLevel()->getFolderName() == $arenaname) {
						$person->sendMessage($this->plugin->prefix."$name viel ins Große Nichts.");
					}
				}
			} else {
				$player->sendMessage(f::BOLD.f::RED."Du bist Gestorben!");
				$lobby = $this->plugin->getServer()->getDefaultLevel();
				$pos = new Position($lobby->getSafeSpawn()->getX(), $lobby->getSafeSpawn()->getY(),
					$lobby->getSafeSpawn()->getZ(), $lobby);
				$player->setSpawn($pos);
				$player->teleport($pos);
				$player->getInventory()->clearAll();
				$players = $this->plugin->getServer()->getOnlinePlayers();
				$counter = 0;
				foreach($players as $person) {
					if($person->getLevel()->getFolderName() == $arenaname) {
						$counter++;
						$pename = $person->getName();
						$pc = new Config("/cloud/users/$pename.yml", Config::YAML);
						$pos = $pc->get("pos");
						$person->sendMessage($this->plugin->prefix."$name ist Ausgeschieden!");
					}
				}
				foreach($players as $person) {
					if ($person->getLevel()->getFolderName() == $arenaname) {
						$person->sendMessage($this->plugin->prefix."Es sind noch $counter Spieler übrig!");
					}
				}
				$this->plugin->getLogger()->info("$name");
				$this->plugin->getScheduler()->cancelTask($this->getTaskId());
			}
		}


	}
}