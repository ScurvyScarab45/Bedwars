<?php

/**
 * @author Fludixx
 * @copyright 2018 Fludixx
 * @version 0.3
 * @license MIT
 *
 */

namespace Fludixx\BedWars;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\FlameParticle;
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

class CobwebTask extends Task
{
	public $plugin;
	public $block;

	public function __construct(Bedwars $plugin, Block $block)
	{

		/**
		 * @param Bedwars $plugin
		 * @param Block $block
		 */

		$this->plugin = $plugin;
		$this->block = $block;
	}

	public function onRun(int $tick)
	{
		$this->block->getLevel()->setBlock(new Position($this->block->getX(),$this->block->getY(),$this->block->getZ
		(),$this->block->getLevel()), Block::get(Block::AIR));
		$this->block->getLevel()->addParticle(new FlameParticle(new Position($this->block->getX(),$this->block->getY(),$this->block->getZ
		(),$this->block->getLevel())));
	}
}