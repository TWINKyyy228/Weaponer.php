<?php

namespace San;

use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\Player;
use \pocketmine\event\player\PlayerInteractEvent; 
use pocketmine\entity\Effect;
use \pocketmine\event\player\PlayerDropItemEvent; 
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\EntityEventPacket; 
use pocketmine\item\enchantment\Enchantment;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\item\ItemIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\entity\{Vindicator, Entity};
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\InventoryNetworkIds;
use pocketmine\network\mcpe\protocol\protocolInfo;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\INVENTORY_ACTION_PACKET;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket; 
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Location;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\level\sound\{ClickSound, AnvilFallSound, PopSound};

use Guild\Main as Guild;


class Weapon extends PluginBase implements Listener{
	
	public $chest = array();
	public $updates = array();

		public function onEnable(){

        @mkdir($this->getDataFolder());

        $this->quests = new Config($this->getDataFolder()."quests.yml", Config::YAML);
        $this->cfg = new Config($this->getDataFolder()."data.yml", Config::YAML);
				$this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->eco = $this->getServer()->getPluginManager()->getPlugin("SanMine-Economy");

        $this->farm = $this->getServer()->getPluginManager()->getPlugin("farmer");
				$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "update")), 5);
		}
		 public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
		 	if($cmd->getName() == "weapon"){
		 		if($p->getGamemode() !== 0) return $p->sendMessage("§6› §7Нельзя открывать в §6Креативе§7!");
if($p->distance(new Vector3(-540, 71, 289)) >= 10) return $p->addTitle('§7Используйте на §6Спавне§7!');
		 		$nick = strtolower($p->getName());
		 		$player = $p;
		 		$this->menu($player,$nick);
		 		$this->chest[$nick] = true;
		 	}
		 }

	  public function Vihod($player){
			   $n = strtolower($player->getName());
               $this->updates[$n] = 10;
		         if(isset($this->chest[$n])){
		          $pk = new ContainerClosePacket();
				  $pk->windowid = 10;
				  unset($this->chest[$n]);
				  $player->dataPacket($pk);
				
		 }
}

    public function onJoin(PlayerJoinEvent $e){
        if(empty($this->quests->get(strtolower($e->getPlayer()->getName())))){
            $quest = $this->quests->get(strtolower($e->getPlayer()->getName())) + 0; 
            $this->quests->set(strtolower($e->getPlayer()->getName()), $quest);
			$this->quests->save();
        }
        if(empty($this->cfg->get(strtolower($e->getPlayer()->getName())))){
            $cnfg = $this->cfg->get(strtolower($e->getPlayer()->getName())) + 0;
            $this->cfg->set(strtolower($e->getPlayer()->getName()), $cnfg);
			$this->cfg->save();
        }
    }
   
		public function menu($player,$nick){ 
			    $pk = new UpdateBlockPacket;
				$pk->x = (int)round($player->x);   
				$pk->y = (int)round($player->y) - (int)3;
				$pk->z = (int)round($player->z);
				$pk->blockId = 54;
				$pk->blockData = 5;
				$player->dataPacket($pk);

				$pk = new UpdateBlockPacket;
				$pk->x = (int)round($player->x);
				$pk->y = (int)round($player->y) - (int)3;
				$pk->z = (int)round($player->z) + (int)1; 
				$pk->blockId = 54;
				$pk->blockData = 5;
				$player->dataPacket($pk);
			 
				$nbt = new CompoundTag("", [
				new StringTag("id", Tile::CHEST),
				new StringTag("CustomName", "§6› §7Оружейник!"),
				new IntTag("x", (int)round($player->x)),
				new IntTag("y", (int)round($player->y) - (int)3),
				new IntTag("z", (int)round($player->z))
				]);
				$tile1 = Tile::createTile("Chest", $player->getLevel(), $nbt);
		
		
			 
				$nbt = new CompoundTag("", [        
					new StringTag("id", Tile::CHEST),
					new StringTag("CustomName", "§6› §7Оружейник!"),
					new IntTag("x", (int)round($player->x)),
					new IntTag("y", (int)round($player->y) - (int)3),
					new IntTag("z", (int)round($player->z) + (int)1)
				]);
				
				
				$tile2 = Tile::createTile("Chest", $player->getLevel(), $nbt);
		
		
		
				$tile1->pairWith($tile2);
				$tile2->pairWith($tile1);
 

					
                $this->updates[$nick] = 1;
            }


          public function update(){
		  foreach($this->updates as $nick => $value){
			  
			  $player = $this->getServer()->getPlayer($nick); 
			  	 $x = (int)round($player->x);
				 $y = (int)round($player->y)-(int)3;   
				 $z = (int)round($player->z);
				    if($this->updates[$nick] == 1) $this->updates[$nick]++; else{
					if($this->updates[$nick] == 2) $this->updates[$nick]++;

					else{
						   if($this->updates[$nick] == 10 or $this->updates[$nick] == 11) return $this->updates[$nick]++;
						   if($this->updates[$nick] == 12){
							   
							   	$block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z));
		
									$pk = new UpdateBlockPacket;
									$pk->x = (int)round($player->x);
									$pk->y = (int)round($player->y)-(int)3;
									$pk->z = (int)round($player->z);
									$pk->blockId = $block->getId();
									$pk->blockData = 0;
									$player->dataPacket($pk);
									
									
									
									$block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z + 1));
								
									$pk = new UpdateBlockPacket;
									$pk->x = (int)round($player->x);
									$pk->y = (int)round($player->y)-(int)3;
									$pk->z = (int)round($player->z) + 1;
									$pk->blockId = $block->getId();
									$pk->blockData = 0;
									$player->dataPacket($pk);
							        unset($this->updates[$nick]);
							   return;
						   }
						   $pk = new ContainerOpenPacket;
						   $pk->windowid = 10;
						   $pk->type = InventoryNetworkIds::CONTAINER;
						   $pk->x = (int)round($player->x);
						   $pk->y = (int)round($player->y) - (int)3;
						   $pk->z = (int)round($player->z);
						   
						   $player->dataPacket($pk);
						   
						   $this->Oruzheynik($player);
						   
						   unset($this->updates[$nick]);
					}
					}
		  }
			
		}
		public function quests($player){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [12,14,13,20,21,22,23,30,31,32];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
						$pk->slots[4] = Item::get(384, 0, 1)->setCustomName("§7Выполнено квестов§8: §6".$this->quests->get(strtolower($player->getName())));
						if($this->quests->get(strtolower($player->getName())) == 0){
							$pk->slots[20] = Item::get(403, 0, 1)->setCustomName("§7Принеси мне §6х64§7 железной руды,\nа то §6доспехи §7уже не из чего делать.\n\nНаграда§8: §65.000$");
						}else{
							$pk->slots[20] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
						}
						if($this->quests->get(strtolower($player->getName())) == 1){
							$pk->slots[21] = Item::get(403, 0, 1)->setCustomName("§7Мне бы не помешало б §6х128§7 палок\nесть много §6работы§7, а §6материала§7 нет.\n\nНаграда§8: §61.000$");
						}else{
							$pk->slots[21] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
						}
						if($this->quests->get(strtolower($player->getName())) == 2){
							$pk->slots[22] = Item::get(403, 0, 1)->setCustomName("§7Как там дела у вас с §6Фермером§7?\nПолучи у него §62§7 уровень.\n\nНаграда§8: §610.000$");
					}else{
						$pk->slots[22] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
					}
if($this->quests->get(strtolower($player->getName())) == 3){
        $pk->slots[23] = Item::get(403, 0, 1)->setCustomName("§7Мне срочно нужно §6х3§7 наковальни.\nИ не спрашивай зачем!\n\nНаграда§8: §67.500$");
    	}else{
    		$pk->slots[23] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 4){
        $pk->slots[24] = Item::get(403, 0, 1)->setCustomName("§7Привет, у меня тут новые\n§6заказы§7 появились, можешь\nмне принести §6х356§7 булыжника.\n\nНаграда§8: §62.500$");
    	}else{
    		$pk->slots[24] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 5){
        $pk->slots[29] = Item::get(403, 0, 1)->setCustomName("§7Здравствуй, у меня тут для тебя\nпоявилось новое задание\nпринеси мне §610х Алмазных Кирок§7.\n\nНаграда§8: §610.000$");
    	}else{
    		$pk->slots[29] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 6){
        $pk->slots[30] = Item::get(403, 0, 1)->setCustomName("§7Думаю было бы очень классно\nсделать себе§6 ларёк§7 из §6тёмного дуба§7,\nно для его создания мне нужен§6 тёмный дуб§7.\nДа побольше, думаю §6х500§7 хватит.\n\nНаграда§8: §625.000$");
    	}else{
    		$pk->slots[30] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 7){
        $pk->slots[31] = Item::get(403, 0, 1)->setCustomName("§7Печки нужно §6топить§7, а уже и нечем.\nДавай ты мне §6угля§7, а я тебе §6железа§7.\nМне нужно §6х128§7 угля.\n\nНаграда§8: §6х64 §7железных блоков + §610.000$");
    	}else{
    		$pk->slots[31] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 8){
        $pk->slots[32] = Item::get(403, 0, 1)->setCustomName("§7Принеси мне §6х128§7 обсидиана,\nбуду из него делать§6 оружие§7 и §6доспехи§7.\n\nНаграда§8: §6х1§7 шалкер + §615.000$");
    	}else{
    		$pk->slots[32] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
if($this->quests->get(strtolower($player->getName())) == 9){
        $pk->slots[33] = Item::get(403, 0, 1)->setCustomName("§7Ох, как же я устал,\nхочу поесть что нибудь вкусненького.\nСлушай, принеси мне §6х128§7 картошки§7.\n\nНаграда§8: §615.000$");
    	}else{
    		$pk->slots[33] = Item::get(340, 0, 1)->setCustomName("§7Этот квест уже §6выполнен §7или §6не доступен§7!");
    	}
						$pk->slots[45] = Item::get(262, 0, 1)->setCustomName("§7Вернуться в §6Главное Меню");
						$player->dataPacket($pk);
						return;
		}
		public function prodazha($player){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [12,14,13,20,21,22,23,30,31,32];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
						$pk->slots[4] = Item::get(371, 0, 1)->setCustomName("§7Общий баланс оружейника§8: §6".$this->cfg->get("money")."§7 монет.");
						$pk->slots[19] = Item::get(61, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §65.000$"); //печки
						$pk->slots[20] = Item::get(392, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §62.000$"); //бульба
						$pk->slots[21] = Item::get(351, 4, 32)->setCustomName("§6Продать\n\n§7Цена§8: §66.500$"); //лазурит
						$pk->slots[22] = Item::get(368, 0, 16)->setCustomName("§6Продать\n\n§7Цена§8: §61.500$"); //эндер перлы
						$pk->slots[23] = Item::get(338, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §64.500$"); //тростник
						$pk->slots[24] = Item::get(49, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §610.000$"); //обсидиан
						$pk->slots[25] = Item::get(263, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §66.000$"); //уголь
						$pk->slots[45] = Item::get(262, 0, 1)->setCustomName("§7Вернуться в §6Главное Меню");
						/*if($this->cfg->get(strtolower($player->getName())) == 1){
						    $pk->slots[29] = Item::get(145, 0, 16)->setCustomName("§6Продать\n\n§7Цена§8: §620.000$"); //наковальня
						    $pk->slots[30] = Item::get(262, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §62.500$"); //стрелы
						}
						if($this->cfg->get(strtolower($player->getName())) == 2){
						    $pk->slots[31] = Item::get(265, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §64.600$"); //железо
						    $pk->slots[32] = Item::get(310, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §61.000$"); //кремень
						}
						if($this->cfg->get(strtolower($player->getName())) == 3){
						    $pk->slots[33] = Item::get(264, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §615.000$"); //алмазы
						    $pk->slots[34] = Item::get(331, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §63.500$"); //редстоун
						    $pk->slots[35] = Item::get(280, 0, 32)->setCustomName("§6Продать\n\n§7Цена§8: §62.350$"); //палки
						}*/
						$player->dataPacket($pk);
						return;
		}
		public function pokupka($player){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [9,10,11,15,16,17,23,24,25,26,27,28,29,30,31,32,33,34,35];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
						$pk->slots[4] = Item::get(371, 0, 1)->setCustomName("§7Общий баланс оружейника§8: §6".$this->cfg->get("money")."§7 монет.");
						if($this->cfg->get(strtolower($player->getName())) == 0){
							if($this->cfg->get("sword0") >= 1){
								$item = Item::get(276, 0, 1);
								$item->setCustomName("§6Купить меч\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("sword0")."§8/§4100§7)§9");
								$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
								$pk->slots[18] = $item;
							}else{
								$pk->slots[18] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
							}

if($this->cfg->get("helmet0") >= 1){
       $item = Item::get(310, 0, 1);
       $item->setCustomName("§6Купить шлем\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet0")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
						$pk->slots[19] = $item;
    	}else{
    		$pk->slots[19] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("armour0") >= 1){
       $item = Item::get(311, 0, 1);
       $item->setCustomName("§6Купить броню\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("armour0")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
						$pk->slots[20] = $item;
    	}else{
    		$pk->slots[20] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("leggins0") >= 1){
       $item = Item::get(312, 0, 1);
       $item->setCustomName("§6Купить штаны\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins0")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
						$pk->slots[21] = $item;
    	}else{
    		$pk->slots[21] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("boots0") >= 1){
       $item = Item::get(313, 0, 1);
       $item->setCustomName("§6Купить ботинки\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("boots0")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
						$pk->slots[22] = $item;
    	}else{
    		$pk->slots[22] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	$item = Item::get(175, 0, 1);
       $item->setCustomName("§6Купить Лук\n\n§7Для разблокировки вам\n§7нужен §61§7 Уровень!");
	$pk->slots[23] = $item;
	$item = Item::get(175, 0, 1);
       $item->setCustomName("§6Купить Стрелы\n\n§7Для разблокировки вам\n§7нужен §61§7 Уровень!");
	$pk->slots[24] = $item;
    	$item = Item::get(175, 0, 1);
       $item->setCustomName("§6Купить Тотем\n\n§7Для разблокировки вам\n§7нужен §61§7 Уровень!");
	$pk->slots[25] = $item;
	$item2 = Item::get(175, 0, 1);
       $item2->setCustomName("§6Купить Меч\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item2->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[27] = $item2;
	$item3 = Item::get(175, 0, 1);
       $item3->setCustomName("§6Купить Шлем\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item3->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[28] = $item3;
	$item4 = Item::get(175, 0, 1);
       $item4->setCustomName("§6Купить Броню\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item4->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[29] = $item4;
	$item5 = Item::get(175, 0, 1);
       $item5->setCustomName("§6Купить Штаны\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item5->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[30] = $item5;
	$item6 = Item::get(175, 0, 1);
       $item6->setCustomName("§6Купить Ботинки\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item6->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[31] = $item6;
						}
						
if($this->cfg->get(strtolower($player->getName())) == 1){

if($this->cfg->get("sword1") >= 1){
       $item = Item::get(276, 0, 1);
       $item->setCustomName("§6Купить меч\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(2));
						$pk->slots[18] = $item;
    	}else{
    		$pk->slots[18] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("helmet1") >= 1){
       $item = Item::get(310, 0, 1);
       $item->setCustomName("§6Купить шлем\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
						$pk->slots[19] = $item;
    	}else{
    		$pk->slots[19] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("armour1") >= 1){
       $item = Item::get(311, 0, 1);
       $item->setCustomName("§6Купить броню\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
						$pk->slots[20] = $item;
    	}else{
    		$pk->slots[20] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("leggins1") >= 1){
       $item = Item::get(312, 0, 1);
       $item->setCustomName("§6Купить штаны\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
						$pk->slots[21] = $item;
    	}else{
    		$pk->slots[21] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("boots1") >= 1){
       $item = Item::get(313, 0, 1);
       $item->setCustomName("§6Купить ботинки\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
						$pk->slots[22] = $item;
    	}else{
    		$pk->slots[22] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
if($this->cfg->get("bow1") >= 1){
       $item = Item::get(261, 0, 1);
       $item->setCustomName("§6Купить лук\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("bow1")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(1));
						$pk->slots[23] = $item;
    	}else{
    		$pk->slots[23] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	
if($this->cfg->get("arrows1") >= 1){
						$pk->slots[24] = Item::get(262, 0, 1)->setCustomName("§6Купить стрелы\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("arrows1")."§8/§4100§7)§9");
    	}else{
    		$pk->slots[24] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	if($this->cfg->get("totems") >= 1){
						$pk->slots[25] = Item::get(450, 0, 1)->setCustomName("§6Купить тотем\n\n§7Цена§8: §630.000$\n§7Наличие§8: §7(§a".$this->cfg->get("totems")."§8/§4100§7)§9");
    	}else{
    		$pk->slots[25] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	$item2 = Item::get(175, 0, 1);
       $item2->setCustomName("§6Купить Меч\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item2->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[27] = $item2;
	$item3 = Item::get(175, 0, 1);
       $item3->setCustomName("§6Купить Шлем\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item3->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[28] = $item3;
	$item4 = Item::get(175, 0, 1);
       $item4->setCustomName("§6Купить Броню\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item4->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[29] = $item4;
	$item5 = Item::get(175, 0, 1);
       $item5->setCustomName("§6Купить Штаны\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item5->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[30] = $item5;
	$item6 = Item::get(175, 0, 1);
       $item6->setCustomName("§6Купить Ботинки\n\n§7Для разблокировки вам\n§7нужен §62§7 Уровень!§9");
       $item6->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
	$pk->slots[31] = $item6;
						}
						if($this->cfg->get(strtolower($player->getName())) == 2){

if($this->cfg->get("sword2") >= 1){
       $item = Item::get(276, 0, 1);
       $item->setCustomName("§6Купить меч\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(3));
						$pk->slots[18] = $item;
    	}else{
    		$pk->slots[18] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("helmet2") >= 1){
       $item = Item::get(310, 0, 1);
       $item->setCustomName("§6Купить шлем\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[19] = $item;
    	}else{
    		$pk->slots[19] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("armour2") >= 1){
       $item = Item::get(311, 0, 1);
       $item->setCustomName("§6Купить броню\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[20] = $item;
    	}else{
    		$pk->slots[20] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("leggins2") >= 1){
       $item = Item::get(312, 0, 1);
       $item->setCustomName("§6Купить штаны\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[21] = $item;
    	}else{
    		$pk->slots[21] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("boots2") >= 1){
       $item = Item::get(313, 0, 1);
       $item->setCustomName("§6Купить ботинки\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[22] = $item;
    	}else{
    		$pk->slots[22] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
if($this->cfg->get("bow2") >= 1){
       $item = Item::get(261, 0, 1);
       $item->setCustomName("§6Купить лук\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("bow2")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(2));
						$pk->slots[23] = $item;
    	}else{
    		$pk->slots[23] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	
if($this->cfg->get("arrows2") >= 1){
						$pk->slots[24] = Item::get(262, 0, 32)->setCustomName("§6Купить стрелы\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("arrows2")."§8/§4100§7)§9");
    	}else{
    		$pk->slots[24] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
						if($this->cfg->get("totems") >= 1){
						$pk->slots[25] = Item::get(450, 0, 1)->setCustomName("§6Купить тотем\n\n§7Цена§8: §630.000$\n§7Наличие§8: §7(§a".$this->cfg->get("totems")."§8/§4100§7)§9");
    	}else{
    		$pk->slots[25] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	// 4 lvl chars armour and sword
    	if($this->cfg->get("sword4") >= 1){
       $item = Item::get(276, 0, 1);
       $item->setCustomName("§6Купить меч\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword4")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(4));
						$pk->slots[27] = $item;
    	}else{
    		$pk->slots[27] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("helmet4") >= 1){
       $item = Item::get(310, 0, 1);
       $item->setCustomName("§6Купить шлем\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet4")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
						$pk->slots[28] = $item;
    	}else{
    		$pk->slots[28] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("armour4") >= 1){
       $item = Item::get(311, 0, 1);
       $item->setCustomName("§6Купить броню\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour4")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
						$pk->slots[29] = $item;
    	}else{
    		$pk->slots[29] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("leggins4") >= 1){
       $item = Item::get(312, 0, 1);
       $item->setCustomName("§6Купить штаны\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins4")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
						$pk->slots[30] = $item;
    	}else{
    		$pk->slots[30] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("boots4") >= 1){
       $item = Item::get(313, 0, 1);
       $item->setCustomName("§6Купить ботинки\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots4")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
						$pk->slots[31] = $item;
    	}else{
    		$pk->slots[31] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    }
						/*if($this->cfg->get(strtolower($player->getName())) == 3){

if($this->cfg->get("sword3") >= 1){
       $item = Item::get(276, 0, 1);
       $item->setCustomName("§6Купить меч\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(3));
						$pk->slots[18] = $item;
    	}else{
    		$pk->slots[18] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("helmet3") >= 1){
       $item = Item::get(310, 0, 1);
       $item->setCustomName("§6Купить шлем\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[19] = $item;
    	}else{
    		$pk->slots[19] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("armour3") >= 1){
       $item = Item::get(311, 0, 1);
       $item->setCustomName("§6Купить броню\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[20] = $item;
    	}else{
    		$pk->slots[20] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("leggins3") >= 1){
       $item = Item::get(312, 0, 1);
       $item->setCustomName("§6Купить штаны\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[21] = $item;
    	}else{
    		$pk->slots[21] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}

if($this->cfg->get("boots3") >= 1){
       $item = Item::get(313, 0, 1);
       $item->setCustomName("§6Купить ботинки\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
						$pk->slots[22] = $item;
    	}else{
    		$pk->slots[22] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
if($this->cfg->get("bow3") >= 1){
       $item = Item::get(261, 0, 1);
       $item->setCustomName("§6Купить лук\n\n§7Цена§8: §615.000$\n§7Наличие§8: §7(§a".$this->cfg->get("bow3")."§8/§4100§7)§9");
       $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(3));
						$pk->slots[23] = $item;
    	}else{
    		$pk->slots[23] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
    	
if($this->cfg->get("arrows3") >= 1){
						$pk->slots[24] = Item::get(262, 0, 32)->setCustomName("§6Купить стрелы\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("arrows3")."§8/§4100§7)§9");
    	}else{
    		$pk->slots[24] = Item::get(371, 0, 1)->setCustomName("§7Данный товар у меня §6выкупили§7!");
    	}
						}*/
						
						$pk->slots[45] = Item::get(262, 0, 1)->setCustomName("§7Вернуться в §6Главное Меню");
						$player->dataPacket($pk);
						return;
		}
		public function Oruzheynik($p){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [12,14,13,20,21,22,23,30,31,32];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
						$pk->slots[20] = Item::get(145, 0, 1)->setCustomName("§7Покупка §6Товаров\n\n§7Тут можно §7купить товары§7 у §6Оружейника\n§7Повышайте §6уровень доверия §7и открывайте\nновые предметы для§6 покупок§7!");

						$pk->slots[22] = Item::get(384, 0, 1)->setCustomName("§7Улучшить §6Оружейника\n\n§7Улучшайте§6 уровень доверия §7с §6Оружейником\n§7чтобы открыть новые предметы для §6торговли§7!\n\nВаш уровень Оружейника§8: §6".$this->cfg->get(strtolower($p->getName())));

						$pk->slots[24] = Item::get(54, 0, 1)->setCustomName("§7Продать свои §6Товары\n\n§7Здесь вы можете §6продать§7 свои товары\nПовышайте §6уровень доверия§7 и открывайте\nновые товары для продажи§6 Оружейнику§7!");

						$p->dataPacket($pk);
						return;
		}
		public function upgrade($player){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [12,14,13,20,21,22,23,30,31,32];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
       if($this->cfg->get(strtolower($player->getName())) == 0){
						$pk->slots[22] = Item::get(403, 0, 1)->setCustomName("§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($player->getName())."§8/§650.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($player->getName()))."§8/§65");
  }
       if($this->cfg->get(strtolower($player->getName())) == 1){
						$pk->slots[22] = Item::get(403, 0, 1)->setCustomName("§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($player->getName())."§8/§6100.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($player->getName()))."§8/§610");
    }

      // if($this->cfg->get(strtolower($player->getName())) == 2){
						//$pk->slots[22] = Item::get(403, 0, 1)->setCustomName("§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($player->getName())."§8/§6300.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($player->getName()))."§8/§615");
  // }

       if($this->cfg->get(strtolower($player->getName())) == 2){
						$pk->slots[22] = Item::get(403, 0, 1)->setCustomName("§7У §6вас§7 максимальный\nуровень §6доверия§7!");
						}

						$player->dataPacket($pk);
						return;
		}

public function menuUpgrade($player){
				  $pk = new ContainerSetContentPacket;
					$pk->windowid = 10;
					$pk->targetEid = -1;
					for($i = 0; $i < 54; $i++){
						$customname = "§7www.sanminepe.ru";
						$itid = 102; $dmg = 0;
						$pustota = [12,14,13,20,21,22,23,30,31,32];
					     if(in_array($i, $pustota)){
							 $itid = 0; 
						 }
						$item = Item::get($itid, $dmg, 1);
						if($customname !== null) $item->setCustomName($customname);
						$pk->slots[$i] = $item;
						$customname = null;
						}
						$pk->slots[20] = Item::get(384, 0, 1)->setCustomName("§7Улучшить §6Оружейника\n\n§7Улучшайте§6 уровень доверия §7с §6Оружейником\n§7чтобы открыть новые предметы для §6торговли§7 и §6покупки§7!");

						$pk->slots[24] = Item::get(403, 0, 1)->setCustomName("§7Открыть меню §6квестов\n\n§7Проходите §6квесты§7, получайте\nкрутые §6награды§7, и\nмного-много §6монет§7.");

						$player->dataPacket($pk);
						return;
		}

		  public function drop(PlayerDropItemEvent $event){
			  $player = $event->getPlayer();
			  $nick = strtolower($player->getName());
			  if(isset($this->chest[$event->getPlayer()->getName()])) $event->setCancelled();
			  if(isset($this->updates[$nick])) $event->setCancelled(true);
		  }
		  public function PacketReceive(DataPacketReceiveEvent $e){
		   $p = $e->getPlayer();
		   $nick = strtolower($p->getName());
		   if($e->getPacket() instanceof ContainerClosePacket){
			  if(isset($this->chest[$nick])){
			  $this->Vihod($p);
			  unset($this->chest[$nick]);
			  }
		   }
		  if($e->getPacket() instanceof INVENTORY_ACTION_PACKET or $e->getPacket() instanceof ContainerSetSlotPacket){
			  $pk = $e->getPacket();
		 $nick = strtolower($p->getName());
		 	 if(!isset($this->chest[$nick])) return false;
			  $item1 = $pk->item;

   if($item1->getCustomName() == "§7Продать свои §6Товары\n\n§7Здесь вы можете §6продать§7 свои товары\nПовышайте §6уровень доверия§7 и открывайте\nновые товары для продажи§6 Оружейнику§7!"){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->prodazha($p);
				   
			   }

   if($item1->getCustomName() == "§7Покупка §6Товаров\n\n§7Тут можно §7купить товары§7 у §6Оружейника\n§7Повышайте §6уровень доверия §7и открывайте\nновые предметы для§6 покупок§7!"){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->pokupka($p);
				   
			   }

   if($item1->getCustomName() == "§7Улучшить §6Оружейника\n\n§7Улучшайте§6 уровень доверия §7с §6Оружейником\n§7чтобы открыть новые предметы для §6торговли§7 и §6покупки§7!"){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->upgrade($p);
				   
			   }

   if($item1->getCustomName() == "§7Открыть меню §6квестов\n\n§7Проходите §6квесты§7, получайте\nкрутые §6награды§7, и\nмного-много §6монет§7."){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->quests($p);
				   
   }
   
   if($item1->getCustomName() == "§7www.sanminepe.ru"){
       $e->setCancelled();
   }
   
   if($item1->getCustomName() == "§7Улучшить §6Оружейника\n\n§7Улучшайте§6 уровень доверия §7с §6Оружейником\n§7чтобы открыть новые предметы для §6торговли§7!\n\nВаш уровень Оружейника§8: §6".$this->cfg->get(strtolower($p->getName()))){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->menuUpgrade($p);
				   
			   }

   if($item1->getCustomName() == "§7Вернуться в §6Главное Меню"){
			$e->setCancelled();
    $p->getLevel()->addSound(new ClickSound($p));

				 $this->Oruzheynik($p);
				   
			   }

			   $m = $this->eco->myMoney($p);

			   if($item1->getCustomName() == "§6Купить стрелы\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("arrows2")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 5000){
            $this->eco->reduceMoney($p, 5000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 5000);
      	 	$this->cfg->set("arrows2", $this->cfg->get("arrows2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$p->getInventory()->addItem(Item::get(262, 0, 32));
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

			         	 if($item1->getCustomName() == "§6Купить лук\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("bow2")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 5000){
            $this->eco->reduceMoney($p, 5000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 5000);
      	 	$this->cfg->set("bow2", $this->cfg->get("bow2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(261, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(1));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
      	 
      	 if($item1->getCustomName() == "§6Купить меч\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword2")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 25000){
            $this->eco->reduceMoney($p, 25000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 25000);
      	 		  $this->cfg->set("sword2", $this->cfg->get("sword2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 			$item = Item::get(276, 0, 1);
      	 			$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(3));
      	 			$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить шлем\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet2")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 25000){
            $this->eco->reduceMoney($p, 25000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 25000);
      	 		  $this->cfg->set("helmet2", $this->cfg->get("helmet2") - 1);
      	 		  $this->cfg->save();
      	 		  $p->getLevel()->addSound(new PopSound($p));
      	 		  $this->Vihod($p);
      	 		  $item = Item::get(310, 0, 1);
      	 		  $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
      	 		  $p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить броню\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour2")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 25000){
            $this->eco->reduceMoney($p, 25000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 25000);
      	 	$this->cfg->set("armour2", $this->cfg->get("armour2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 		$item = Item::get(311, 0, 1);
      	 		$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
      	 		$p->getInventory()->addItem($item);
       }else{
                  $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
      	 		  $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить штаны\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins2")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 25000){
            $this->eco->reduceMoney($p, 25000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 25000);
      	 	$this->cfg->set("leggins2", $this->cfg->get("leggins2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(312, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить ботинки\n\n§7Цена§8: §625.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots2")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 25000){
            $this->eco->reduceMoney($p, 25000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 25000);
      	 	$this->cfg->set("boots2", $this->cfg->get("boots2") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(313, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(3));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

			   // upgrade 1 lvl

	 if($item1->getCustomName() == "§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($p->getName())."§8/§650.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($p->getName()))."§8/§65"){
          $e->setCancelled();
      	if($this->cfg->get(strtolower($p->getName())) == 0){
      	if($this->eco->myMoney($p) >= 50000 and $this->quests->get(strtolower($p->getName())) >= 5){
      		$this->cfg->set(strtolower($p->getName()), 1);
      		$this->cfg->save();
      		$this->eco->reduceMoney($p, 50000, " ");
      		$this->Vihod($p);
      		$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Оружейник §6успешно§7 прокачан!");
      		}else{
      		$this->Vihod($p);
			$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия.", 30, 30);
			$this->Vihod($p);
      		}
      	}
      }

      			   // upgrade 2 lvl

	 if($item1->getCustomName() == "§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($p->getName())."§8/§6100.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($p->getName()))."§8/§610"){
          $e->setCancelled();
      	if($this->cfg->get(strtolower($p->getName())) == 1){
      	if($this->eco->myMoney($p) >= 100000 and $this->quests->get(strtolower($p->getName())) >= 10){
      		$this->cfg->set(strtolower($p->getName()), 2);
      		$this->cfg->save();
      		$this->eco->reduceMoney($p, 100000, " ");
      		$this->Vihod($p);
      		$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Оружейник §6успешно§7 прокачан!");
      		}else{
      		$this->Vihod($p);
			$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия.", 30, 30);
			$this->Vihod($p);
      		}
      	}
      }

      			   // upgrade 3 lvl

	 if($item1->getCustomName() == "§7Условия для прокачки §6Оружейника§8:§7\n\nМонет§8: §6$".$this->eco->myMoney($p->getName())."§8/§6300.000$\n§7Выполнить квестов§8:§6 ".$this->quests->get(strtolower($p->getName()))."§8/§615"){
          $e->setCancelled();
      	if($this->cfg->get(strtolower($p->getName())) == 2){
      	if($this->eco->myMoney($p) >= 300000 and $this->quests->get(strtolower($p->getName())) >= 10){
      		$this->cfg->set(strtolower($p->getName()), 3);
      		$this->cfg->save();
      		$this->eco->reduceMoney($p, 300000, " ");
      		$this->Vihod($p);
      		$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Оружейник §6успешно§7 прокачан!");
      		}else{
      		$this->Vihod($p);
			$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия.", 30, 30);
			$this->Vihod($p);
      		}
      	}
      }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §65.000$"){ 
          $e->setCancelled();
            if($this->cfg->get("money") <= 5000){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(61, 0, 32))){
                $this->eco->addMoney($p, 5000, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 5000);
				$this->cfg->save();
                $p->getInventory()->removeItem(Item::get(61, 0, 32));
            $this->Vihod($p);
            $p->getLevel()->addSound(new PopSound($p));
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Печки", 30, 30);
            }else{
            $this->Vihod($p);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Печки§7!", 30, 30);
            }
            return;
        }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §62.000$"){ 
          $e->setCancelled();
            if($this->cfg->get("money") <= 2000){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(392, 0, 32))){
                $this->eco->addMoney($p, 2000, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 2000);
				$this->cfg->save();
                $p->getInventory()->removeItem(Item::get(392, 0, 32));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Картошки", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Картошки§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            }
            return;
        }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §66.500$"){ 
          $e->setCancelled();
            if($this->cfg->get("money") <= 65000){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(351, 4, 32))){
                $this->eco->addMoney($p, 6500, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 6500);
				$this->cfg->save();
                $p->getInventory()->removeItem(Item::get(351, 4, 32));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Лазурита", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Лазурита§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            }
            return;
        }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §61.500$"){
          $e->setCancelled();
            if($this->cfg->get("money") <= 1500){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(61, 0, 32))){
                $this->eco->addMoney($p, 1500, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 1500);
 				$this->cfg->save();               $p->getInventory()->removeItem(Item::get(368, 0, 16));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х16 Эндер-Пёрлов", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х16 Эндер-Пёрлов§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            }
            return;
        }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §64.500$"){
          $e->setCancelled();
            if($this->cfg->get("money") <= 4500){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(338, 0, 32))){
                $this->eco->addMoney($p, 4500, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 4500);
   				$this->cfg->save();             $p->getInventory()->removeItem(Item::get(338, 0, 32));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Тростника", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Тростника§7!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }
            return;
        }

      if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §610.000$"){ 
          $e->setCancelled();
            if($this->cfg->get("money") <= 10000){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(49, 0, 32))){
                $this->eco->addMoney($p, 10000, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 10000);
 				$this->cfg->save();               $p->getInventory()->removeItem(Item::get(49, 0, 32));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Обидиана", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Обсидиана§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            }
            return;
        }

     if($item1->getCustomName() == "§6Продать\n\n§7Цена§8: §66.000$"){ 
         $e->setCancelled();
            if($this->cfg->get("money") <= 6000){
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У §6Оружейника §7закончился§6 баланс", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            return;
            }
            if($p->getInventory()->contains(Item::get(263, 0, 32))){
                $this->eco->addMoney($p, 6000, " ");
                $this->cfg->set("money", $this->cfg->get("money") - 6000);
  				$this->cfg->save();              $p->getInventory()->removeItem(Item::get(263, 0, 32));
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Вы успешно продали §6х32 Угля", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7У вас нету§6 х32 Угля§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
            }
            return;
        }

      if($item1->getCustomName() == "§7У меня кончились материалы\nПринеси мне §6х64§7 железной руды и §6х64§7 золотой руды.\n\n\nНаграда§8: §65.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(15, 0, 64)) || $p->getInventory()->contains(Item::get(14, 0, 64))){
            $p->getInventory()->removeItem(Item::get(15, 0, 64));
            $p->getInventory()->removeItem(Item::get(14, 0, 64));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Железная руда передана, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 6000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Мне бы не помешало б §6х128§7 палок\nесть много §6работы§7, а §6материала§7 нет.\n\nНаграда§8: §61.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(280, 0, 128))){
            $p->getInventory()->removeItem(Item::get(280, 0, 128));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Палки переданы, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 1000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Как там дела у вас с §6Фермером§7?\nПолучи у него §62§7 уровень.\n\nНаграда§8: §610.000$"){
          $e->setCancelled();
        if($this->farm->cfg->get(strtolower($p->getName())) >= 2){
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
             $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§62 §7уровень у §6Фермера§7 есть, держите свою §6награду!", 30, 30);
            $this->eco->addMoney($p, 10000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Мне срочно нужно §6х3§7 наковальни.\nИ не спрашивай зачем!\n\nНаграда§8: §67.500$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(145, 0, 3))){
            $p->getInventory()->removeItem(Item::get(145, 0, 3));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Наковальня передана, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 4500, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Привет, у меня тут новые\n§6заказы§7 появились, можешь\nмне принести §6х356§7 булыжника.\n\nНаграда§8: §62.500$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(4, 0, 356))){
            $p->getInventory()->removeItem(Item::get(4, 0, 356));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Булыжник передан, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 2500, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Здравствуй, у меня тут для тебя\nпоявилось новое задание\nпринеси мне §610х Алмазных Кирок§7.\n\nНаграда§8: §610.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(278, 0, 10))){
            $p->getInventory()->removeItem(Item::get(278, 0, 10));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§710 Кирок передано, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 10000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Думаю было бы очень классно\nсделать себе§6 ларёк§7 из §6тёмного дуба§7,\nно для его создания мне нужен§6 тёмный дуб§7.\nДа побольше, думаю §6х500§7 хватит.\n\nНаграда§8: §625.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(162, 1, 500))){
            $p->getInventory()->removeItem(Item::get(162, 1, 500));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Тёмный дуб передан, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 25000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Печки нужно §6топить§7, а уже и нечем.\nДавай ты мне §6угля§7, а я тебе §6железа§7.\nМне нужно §6х128§7 угля.\n\nНаграда§8: §6х64 §7железных блоков + §610.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(263, 0, 128))){
            $p->getInventory()->removeItem(Item::get(263, 0, 128));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Уголь передан, держите свою §6награду!", 30, 30);
$p->getInventory()->addItem(Item::get(15, 0, 64));
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 10000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }

      if($item1->getCustomName() == "§7Принеси мне §6х128§7 обсидиана,\nбуду из него делать§6 оружие§7 и §6доспехи§7.\n\nНаграда§8: §6х1§7 шалкер + §615.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(49, 0, 128))){
            $p->getInventory()->removeItem(Item::get(49, 0, 128));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Обсидиан передан, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 5000, " ");
$p->getInventory()->addItem(Item::get(218, 0, 1));
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }
            	 // buy armor 4lvl

      	       	 if($item1->getCustomName() == "§6Купить меч\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword4")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 40000){
            $this->eco->reduceMoney($p, 40000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 40000);
      	 		  $this->cfg->set("sword4", $this->cfg->get("sword4") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 			$item = Item::get(276, 0, 1);
      	 			$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(4));
      	 			$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
      	  
      	
      	     if($item1->getCustomName() == "§6Купить шлем\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet4")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 40000){
            $this->eco->reduceMoney($p, 40000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 40000);
      	 		  $this->cfg->set("helmet4", $this->cfg->get("helmet4") - 1);
      	 		  $this->cfg->save();
      	 		  $p->getLevel()->addSound(new PopSound($p));
      	 		  $this->Vihod($p);
      	 		  $item = Item::get(310, 0, 1);
      	 		  $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
      	 		  $p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить броню\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour4")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 40000){
            $this->eco->reduceMoney($p, 40000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 40000);
      	 	$this->cfg->set("armour4", $this->cfg->get("armour4") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 		$item = Item::get(311, 0, 1);
      	 		$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
      	 		$p->getInventory()->addItem($item);
       }else{
                  $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
      	 		  $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить штаны\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins4")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 40000){
            $this->eco->reduceMoney($p, 40000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 40000);
      	 	$this->cfg->set("leggins4", $this->cfg->get("leggins4") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(312, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить ботинки\n\n§7Цена§8: §640.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots4")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 40000){
            $this->eco->reduceMoney($p, 40000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 40000);
      	 	$this->cfg->set("boots4", $this->cfg->get("boots4") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(313, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(4));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	 if($item1->getCustomName() == "§6Купить тотем\n\n§7Цена§8: §630.000$\n§7Наличие§8: §7(§a".$this->cfg->get("totems")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 30000){
            $this->eco->reduceMoney($p, 30000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 30000);
      	 		  $this->cfg->set("totems", $this->cfg->get("totems") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 			$item = Item::get(450, 0, 1);
      	 			$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      if($item1->getCustomName() == "§7Ох, как же я устал,\nхочу поесть что нибудь вкусненького.\nСлушай, принеси мне §6х128§7 картошки§7.\n\nНаграда§8: §615.000$"){
          $e->setCancelled();
        if($p->getInventory()->contains(Item::get(392, 0, 128))){
            $p->getInventory()->removeItem(Item::get(392, 0, 128));
            $this->Vihod($p);
            $this->quests->set(strtolower($p->getName()), $this->quests->get(strtolower($p->getName())) + 1);
            $this->quests->save();
            if(Guild::isInGuild($p)){
                $guild = Guild::getPlayerGuild($p);
                $guild['xp'] = $guild['xp'] + 750;
                Guild::$guilds->set(strtolower($guild['name']), $guild);
   		Guild::$guilds->save();
            }
            $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Картошка передана, держите свою §6награду!", 30, 30);
            $p->getLevel()->addSound(new PopSound($p));
            $this->eco->addMoney($p, 15000, " ");
        }else{
            $this->Vihod($p);
            $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Вы не §6выполнили§7 все условия!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
        }
        return;
      }
      	     $m = $this->eco->myMoney($p);
      	     if($item1->getCustomName() == "§6Купить меч\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("sword0")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 2500){
            $this->eco->reduceMoney($p, 2500, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 2500);
      	 		  $this->cfg->set("sword0", $this->cfg->get("sword0") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 			$item = Item::get(276, 0, 1);
      	 			$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
      	 			$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить шлем\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet0")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 2500){
            $this->eco->reduceMoney($p, 2500, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 2500);
      	 		  $this->cfg->set("helmet0", $this->cfg->get("helmet0") - 1);
      	 		  $this->cfg->save();
      	 		  $p->getLevel()->addSound(new PopSound($p));
      	 		  $this->Vihod($p);
      	 		  $item = Item::get(310, 0, 1);
      	 		  $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
      	 		  $p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить броню\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("armour0")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 2500){
            $this->eco->reduceMoney($p, 2500, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 2500);
      	 	$this->cfg->set("armour0", $this->cfg->get("armour0") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 		$item = Item::get(311, 0, 1);
      	 		$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
      	 		$p->getInventory()->addItem($item);
       }else{
                  $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
      	 		  $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить штаны\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins0")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 2500){
            $this->eco->reduceMoney($p, 2500, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 2500);
      	 	$this->cfg->set("leggins0", $this->cfg->get("leggins0") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(312, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
      	 
      	 if($item1->getCustomName() == "§6Купить стрелы\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("arrows1")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 5000){
            $this->eco->reduceMoney($p, 5000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 5000);
      	 	$this->cfg->set("arrows1", $this->cfg->get("arrows1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$p->getInventory()->addItem(Item::get(262, 0, 32));
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить ботинки\n\n§7Цена§8: §62.500$\n§7Наличие§8: §7(§a".$this->cfg->get("boots0")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 2500){
            $this->eco->reduceMoney($p, 2500, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 2500);
      	 	$this->cfg->set("boots0", $this->cfg->get("boots0") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(313, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
      	 
      	 if($item1->getCustomName() == "§6Купить лук\n\n§7Цена§8: §65.000$\n§7Наличие§8: §7(§a".$this->cfg->get("bow1")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 5000){
            $this->eco->reduceMoney($p, 5000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 5000);
      	 	$this->cfg->set("bow1", $this->cfg->get("bow1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(261, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(1));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
      	 
      	 if($item1->getCustomName() == "§6Купить меч\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("sword1")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 10000){
            $this->eco->reduceMoney($p, 10000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 10000);
      	 		  $this->cfg->set("sword1", $this->cfg->get("sword1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 			$item = Item::get(276, 0, 1);
      	 			$item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(2));
      	 			$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить шлем\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("helmet1")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 10000){
            $this->eco->reduceMoney($p, 10000, " ");
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 		  $this->cfg->set("money", $this->cfg->get("money") + 10000);
      	 		  $this->cfg->set("helmet1", $this->cfg->get("helmet1") - 1);
      	 		  $this->cfg->save();
      	 		  $p->getLevel()->addSound(new PopSound($p));
      	 		  $this->Vihod($p);
      	 		  $item = Item::get(310, 0, 1);
      	 		  $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
      	 		  $p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

      	     if($item1->getCustomName() == "§6Купить броню\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("armour1")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 10000){
            $this->eco->reduceMoney($p, 10000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 10000);
      	 	$this->cfg->set("armour1", $this->cfg->get("armour1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 		$item = Item::get(311, 0, 1);
      	 		$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
      	 		$p->getInventory()->addItem($item);
       }else{
                  $this->Vihod($p);
      	 		  $p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
      	 		  $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить штаны\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("leggins1")."§8/§4100§7)§9"){
      	    	$e->setCancelled();
      	 	if($m >= 10000){
            $this->eco->reduceMoney($p, 10000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇪", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 10000);
      	 	$this->cfg->set("leggins1", $this->cfg->get("leggins1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(312, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }

     	     if($item1->getCustomName() == "§6Купить ботинки\n\n§7Цена§8: §610.000$\n§7Наличие§8: §7(§a".$this->cfg->get("boots1")."§8/§4100§7)§9"){
      	    $e->setCancelled();
      	 	if($m >= 10000){
            $this->eco->reduceMoney($p, 10000, " ");
      	 	$p->addTitle("§6ОРУЖЕЙНИК§a⇩", "§7Спасибо за покупку §6товара§7!", 30, 30);
      	 	$this->cfg->set("money", $this->cfg->get("money") + 10000);
      	 	$this->cfg->set("boots1", $this->cfg->get("boots1") - 1);
            $this->cfg->save();
            $p->getLevel()->addSound(new PopSound($p));
            $this->Vihod($p);
      	 	$item = Item::get(313, 0, 1);
      	 	$item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(2));
      	 	$p->getInventory()->addItem($item);
       }else{
            $this->Vihod($p);
      	 	$p->addTitle("§6ОРУЖЕЙНИК§c⇩", "§7Недостаточно §6монет§7!", 30, 30);
            $p->getLevel()->addSound(new AnvilFallSound($p));
      	 	}
      	 	return;
      	 }
		  }
	  }
}
