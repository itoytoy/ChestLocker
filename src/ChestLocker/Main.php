<?php

/*
 * ChestLocker (v1.2) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 27/12/2014 03:32 PM (UTC)
 * Copyright & License: (C) 2014-2017 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ChestLocker/blob/master/LICENSE)
 */

namespace ChestLocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandExecutor;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerRespawnEvent;

use pocketmine\math\Vector3;
use pocketmine\tile\Chest;

class Main extends PluginBase implements Listener {
	
	//About Plugin Const
	const PRODUCER = "EvolSoft";
	const VERSION = "1.2";
	const MAIN_WEBSITE = "http://www.evolsoft.tk";
	//Other Const
	//Prefix
	
	const PREFIX = "&8[&4Chest&6Locker&8] ";
	
	//TODO: This is a mess
	const _FILE = ".";
	const _DIRECTORY = "chests/";
	//Item Name/ID
	const ITEM_NAME = "Chest";
	const ITEM_NAME_2 = "chest";
	const ITEM_ID = 54;
	
	public $status;
	public $data;

	public function translateColors($symbol, $message){
		$message = str_replace($symbol."0", TextFormat::BLACK, $message);
		$message = str_replace($symbol."1", TextFormat::DARK_BLUE, $message);
		$message = str_replace($symbol."2", TextFormat::DARK_GREEN, $message);
		$message = str_replace($symbol."3", TextFormat::DARK_AQUA, $message);
		$message = str_replace($symbol."4", TextFormat::DARK_RED, $message);
		$message = str_replace($symbol."5", TextFormat::DARK_PURPLE, $message);
		$message = str_replace($symbol."6", TextFormat::GOLD, $message);
		$message = str_replace($symbol."7", TextFormat::GRAY, $message);
		$message = str_replace($symbol."8", TextFormat::DARK_GRAY, $message);
		$message = str_replace($symbol."9", TextFormat::BLUE, $message);
		$message = str_replace($symbol."a", TextFormat::GREEN, $message);
		$message = str_replace($symbol."b", TextFormat::AQUA, $message);
		$message = str_replace($symbol."c", TextFormat::RED, $message);
		$message = str_replace($symbol."d", TextFormat::LIGHT_PURPLE, $message);
		$message = str_replace($symbol."e", TextFormat::YELLOW, $message);
		$message = str_replace($symbol."f", TextFormat::WHITE, $message);
		
		$message = str_replace($symbol."k", TextFormat::OBFUSCATED, $message);
		$message = str_replace($symbol."l", TextFormat::BOLD, $message);
		$message = str_replace($symbol."m", TextFormat::STRIKETHROUGH, $message);
		$message = str_replace($symbol."n", TextFormat::UNDERLINE, $message);
		$message = str_replace($symbol."o", TextFormat::ITALIC, $message);
		$message = str_replace($symbol."r", TextFormat::RESET, $message);
		
		return $message;
	}
	
    public function onEnable(){
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . Main::_DIRECTORY);
        $this->saveDefaultConfig();
		$this->data = $this->getDataFolder();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
        // $this->getCommand("chestlocker")->setExecutor(new Commands\Commands($this));
        // $this->getCommand("lockchest")->setExecutor(new Commands\LockChest($this));
        // $this->getCommand("unlockchest")->setExecutor(new Commands\UnlockChest($this));
	    // $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
    
    public function setCommandStatus($int, $player){
    	//0 Empty
    	//1 Lock
    	//2 Unlock
    	if($int >= 0 && $int <= 3){
    		$this->status[strtolower($player)] = $int;
    	}
    }
    
    public function getCommandStatus($player){
    	if(isset($this->status[strtolower($player)])){
    		return $this->status[strtolower($player)];
    	}else{
    		$this->status[strtolower($player)] = 0;
    		return $this->status[strtolower($player)];
    	}
    }
    
    public function endCommandSession($player){
    	unset($this->status[strtolower($player)]);
    }
    
    public function isChestRegistered($level, $x, $y, $z){
    	return file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"));
    }
    
    public function getChestOwner($level, $x, $y, $z){
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"))){
    		$chest = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"), Config::YAML);
    		$tmp = $chest->get("player");
    		return strtolower($tmp); //Success!
    	}else{
    		return false; //Failed: Chest not registered
    	}
    }
    
    public function lockChest($level, $x, $y, $z, $player){
    	@mkdir($this->data . Main::_DIRECTORY . strtolower($level . "/"));
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"))){
    		return false; //Error: Chest already registered
    	}else{
    		$chest = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE. $z . ".yml"), Config::YAML);
    		$chest->set("player", $player);
    		$chest->save();
    		return true; //Success!
    	}
    }
    
    public function unlockChest($level, $x, $y, $z, $player){
    	if(file_exists($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"))){
    		$chest = new Config($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y . Main::_FILE . $z . ".yml"), Config::YAML);
    		$tmp = $chest->get("player");
    	    if(strtolower($player)==strtolower($tmp)){
    	    	unlink($this->data . Main::_DIRECTORY . strtolower($level . "/") . strtolower($x . Main::_FILE . $y.Main::_FILE . $z . ".yml"));
    	    	return 2; //Success!
    	    }else{
    	    	return 1; //Failed: Player is not owner of chest
    	    }
    	}else{
    		return 0; //Failed: Chest not registered
    	}
	}
	

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    	switch(strtolower($command->getName())) {
            case "chestlocker": {
                if (isset($args[0])) {
                    $args[0] = strtolower($args[0]);
                    if ($args[0] == "help") {
                        if ($sender->hasPermission("chestlocker.commands.help")) {
                            $sender->sendMessage($this->translateColors("&", "&c|| &8Available Commands &c||"));
                            $sender->sendMessage($this->translateColors("&", "&c/chlock info &8> Show info about this plugin"));
                            $sender->sendMessage($this->translateColors("&", "&c/chlock reload &8> Reload the config"));
                            $sender->sendMessage($this->translateColors("&", "&c/lockchest &8> Lock a " . Main::ITEM_NAME_2));
                            $sender->sendMessage($this->translateColors("&", "&c/unlockchest &8> Unlock a " . Main::ITEM_NAME_2));
                            break;
                        } else {
                            $sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
                            break;
                        }
                    } elseif ($args[0] == "reload") {
                        if ($sender->hasPermission("chestlocker.commands.reload")) {
                            $this->reloadConfig();
                            $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&aConfiguration Reloaded."));
                            break;
                        } else {
                            $sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
                            break;
                        }
                    } elseif ($args[0] == "info") {
                        if ($sender->hasPermission("chestlocker.commands.info")) {
                            $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&8ChestLocker &cv" . Main::VERSION . " &8developed by&c " . Main::PRODUCER));
                            $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&8Website &c" . Main::MAIN_WEBSITE));
                            break;
                        } else {
                            $sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
                            break;
                        }
                    }
                } else {
                    if ($sender->hasPermission("chestlocker.commands.help")) {
                        $sender->sendMessage($this->translateColors("&", "&c|| &8Available Commands &c||"));
                        $sender->sendMessage($this->translateColors("&", "&c/chlock info &8> Show info about this plugin"));
                        $sender->sendMessage($this->translateColors("&", "&c/chlock reload &8> Reload the config"));
                        $sender->sendMessage($this->translateColors("&", "&c/lockchest &8> Lock a " . Main::ITEM_NAME_2));
                        $sender->sendMessage($this->translateColors("&", "&c/unlockchest &8> Unlock a " . Main::ITEM_NAME_2));
                        break;
                    } else {
                        $sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
                        break;
                    }
                }
                return true;
			}
			
			case "unlockchest": {
                if ($sender->hasPermission("chestlocker.commands.unlockchest")) {
                    //Player Sender
                    if ($sender instanceof Player) {
                        if ($this->getCommandStatus($sender->getName()) == 0 || $this->getCommandStatus($sender->getName()) == 1) {
                            $this->setCommandStatus(2, $sender->getName());
                            $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " unlock command enabled. Click the " . Main::ITEM_NAME_2 . " to unlock"));
                        } else {
                            $this->setCommandStatus(0, $sender->getName());
                            $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&4" . Main::ITEM_NAME . " unlock command disabled."));
                        }
                    } //Console Sender
                    else {
                        $sender->sendMessage($this->translateColors("&", Main::PREFIX . "&cYou can only perform this command as a player"));
                        return true;
                    }
                } else {
                    $sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
                    break;
                }
                return true;
			}
			
			case "lockchest":
            {
				if($sender->hasPermission("chestlocker.commands.lockchest")){
					//Player Sender
					if($sender instanceof Player){
						if($this->getCommandStatus($sender->getName()) == 0 || $this->getCommandStatus($sender->getName()) == 2){
							$this->setCommandStatus(1, $sender->getName());
							$sender->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " lock command enabled. Click the " . Main::ITEM_NAME_2 . " to lock"));
						}else{
							$this->setCommandStatus(0, $sender->getName());
							$sender->sendMessage($this->translateColors("&", Main::PREFIX . "&4" . Main::ITEM_NAME . " lock command disabled."));
						}
					}
					//Console Sender
					else{
						$sender->sendMessage($this->translateColors("&", Main::PREFIX . "&cYou can only perform this command as a player"));
						return true;
					}
				}else{
					$sender->sendMessage($this->translateColors("&", "&cYou don't have permissions to use this command"));
					break;
				}
				return true;
            }


            default:
                return false;
        }
    }
	
	
	public function onPlayerJoin(PlayerJoinEvent $event) {
        $this->setCommandStatus(0, $event->getPlayer()->getName());
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $this->endCommandSession($event->getPlayer()->getName());
    }

    public function onChestOpen(PlayerInteractEvent $event) {
        if ($event->getBlock()->getID() == Main::ITEM_ID) {
            $chest = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if ($chest instanceof Chest) {
                //Check Command status
                //0
                if ($this->getCommandStatus($event->getPlayer()->getName()) == 0) {
                    //Check if Chest is registered
                    $paired = $chest->getPair();
                    if ($this->isChestRegistered($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()) && $this->getChestOwner($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()) != strtolower($event->getPlayer()->getName()) || $paired != null && $this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) && $this->getChestOwner($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) != strtolower($event->getPlayer()->getName())) {

                        $event->setCancelled(true);
                        $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
                    }
                }

                //1
                if ($this->getCommandStatus($event->getPlayer()->getName()) == 1) {
                    //Check if Chest is registered
                    $paired = $chest->getPair();
                    if ($this->isChestRegistered($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ())) {
                        if ($this->getChestOwner($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()) != strtolower($event->getPlayer()->getName()) || $paired != null && $this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) && $this->getChestOwner($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) != strtolower($event->getPlayer()->getName())) {
                            $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
                        } else {
                            $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " already locked"));
                        }
                    } else {
                        $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " locked"));
                        $this->lockChest($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ(), $event->getPlayer()->getName());
                        if ($paired != null && !($this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()))) {
                            $this->lockChest($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ(), $event->getPlayer()->getName());
                        }
                    }
                    $event->setCancelled(true);
                    $this->setCommandStatus(0, $event->getPlayer()->getName());
                }
                //2
                if ($this->getCommandStatus($event->getPlayer()->getName()) == 2) {
                    //Check if Chest is registered
                    if ($this->isChestRegistered($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ())) {
                        if ($this->getChestOwner($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()) != strtolower($event->getPlayer()->getName())) {
                            $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
                        } else {
                            $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " unlocked"));
                            $this->unlockChest($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ(), $this->getChestOwner($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()));
                            $paired = $chest->getPair();
                            if ($paired != null && $this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ())) {
                                $this->unlockChest($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ(), $this->getChestOwner($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()));
                            }
                        }
                    } else {
                        $event->getPlayer()->sendMessage($this->translateColors("&", Main::PREFIX . "&2" . Main::ITEM_NAME . " not registered"));
                    }
                    $event->setCancelled(true);
                    $this->setCommandStatus(0, $event->getPlayer()->getName());
                }
            }
        }
    }

    public function onBlockDestroy(BlockBreakEvent $event) {
        $this->cfg = $this->getConfig()->getAll();
        $player = $event->getPlayer();
        if ($event->getBlock()->getID() == Main::ITEM_ID) {
            $chest = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if ($chest instanceof Chest) {
                $level = $chest->getLevel()->getName();
                $x = $chest->getX();
                $y = $chest->getY();
                $z = $chest->getZ();
                $paired = $chest->getPair();
                //Check if chest is registered
                if ($this->isChestRegistered($level, $x, $y, $z)) {
                    if (($this->isChestRegistered($chest->getLevel()->getName(), $chest->getX(), $chest->getY(), $chest->getZ()) && $this->getChestOwner($level, $x, $y, $z) != strtolower($event->getPlayer()->getName()) || $paired != null && $this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) && $this->getChestOwner($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()) != strtolower($event->getPlayer()->getName()))) {
                        $player->sendMessage($this->translateColors("&", Main::PREFIX . "&4You aren't the owner of this " . Main::ITEM_NAME_2 . "."));
                        $event->setCancelled(true);
                    } else {
                        $this->unlockChest($level, $x, $y, $z, $this->getChestOwner($level, $x, $y, $z));
                        if ($paired != null && $this->isChestRegistered($paired->getLevel()->getName(), $paired->getX(), $paired->getY(), $paired->getZ()))
                            $this->unlockChest($level, $paired->getX(), $y, $paired->getZ(), $this->getChestOwner($level, $paired->getX(), $y, $paired->getZ()));
                    }
                }
            }
        }
    }
}
