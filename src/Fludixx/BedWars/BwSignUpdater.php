<?php

namespace Fludixx\BedWars;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Clay;
use pocketmine\block\HardenedClay;
use pocketmine\block\Sandstone;
use pocketmine\block\StainedClay;
use pocketmine\level\Position;
use pocketmine\Server;
use Fludixx\BedWars\Bedwars;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as f;

class BwSignUpdater extends Task
{
	public $plugin;
	public $sign;

	public function __construct(Bedwars $plugin, \pocketmine\tile\Sign $sign)
	{
		/**
		 * @param Bedwars $plugin
		 * @param \pocketmine\tile\Sign $sign
		 */

		$this->plugin = $plugin;
		$this->sign = $sign;
	}

	public function onRun(int $tick)
	{
		$sign = $this->sign;
		$text = $sign->getText();
		$levelname = $text['3'];
		$c = new Config("/cloud/bw/$levelname.yml", Config::YAML);
		$busy = (string)$c->get("busy");
		$this->plugin->getServer()->loadLevel($levelname);
		$level = $this->plugin->getServer()->getLevelByName($levelname);
		$level->setAutoSave(false);
		$players = $this->plugin->getServer()->getOnlinePlayers();
		$counter = 0;
		foreach($players as $player) {
			if($player->getLevel()->getFolderName() == $levelname) {
				$counter++;
			}
		}
		if($counter == 1 || $counter == 0) {
			$c->set("busy", false);
			$c->save();
		}
		$dimension = $c->get("dimension");
		$sign->setLine(1, f::DARK_GRAY."[".f::GREEN."$dimension".f::DARK_GRAY."]");
		$playeramout = eval("return $dimension;");
		$sign->setLine(2, f::YELLOW."$counter ".f::DARK_GRAY."/ ".f::GREEN."$playeramout");

		$restart = $c->get("restart");
		if($restart) {
			$c->set("restart", false);
			$c->save();
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}

		if($busy) {
			$sign->setLine(0, f::RED."Bedwars");
			$pos = new Position($sign->asPosition()->getX() - 1, $sign->asPosition()->getY(), $sign->asPosition()->getZ(), $sign->getLevel());
			$block = $sign->getLevel()->getBlock($pos);
			if ($block instanceof StainedClay ||
				$block instanceof HardenedClay ||
				$block instanceof Clay ||
				$block instanceof Sandstone) {
				$sign->getLevel()->setBlock($pos, Block::get(Block::STAINED_CLAY, 14));
			}
		}
		if(!$busy) {
			$sign->setLine(0, $this->plugin::NAME);
			$pos = new Position($sign->asPosition()->getX() - 1, $sign->asPosition()->getY(), $sign->asPosition()->getZ(), $sign->getLevel());
			$block = $sign->getLevel()->getBlock($pos);
			if ($block instanceof StainedClay ||
				$block instanceof HardenedClay ||
				$block instanceof Clay ||
				$block instanceof Sandstone) {
				$sign->getLevel()->setBlock($pos, Block::get(Block::STAINED_CLAY, 5));
			}
		}
	}
}