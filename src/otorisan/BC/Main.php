<?php

namespace otorisan\BC;

use Flowy\Flowy;
use function Flowy\delay;
use function Flowy\listen;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Villager;
use pocketmine\math\Vector3;
use pocketmine\utils\UUID;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;

class Main extends Flowy
{

    function onEnable()
    {
        date_default_timezone_set("Asia/Tokyo");
        $this->start($this->chatToTapUser());
    }

    function chatToTapUser()
    {
        //PlayerInteractEvent待機
        $tap = yield listen (PlayerInteractEvent::class)
            ->filter(function ($ev) { return
                $ev->getBlock()->getId() == BlockIds::WOODEN_BUTTON;
            });

        //処理対象の取得
        $player = $tap->getPlayer();
        $player->sendMessage("何か知りたいことはありますか？\n  §7> 日時: #1\n  > 人数: #2\n  > 参加者: #3\n  > 鯖: #4\n  > ない: #5\n");

        //PlayerChatEventでのやり取り
        while (true) {

            //PlayerChatEvent待機 (メッセージが該当の物のうちどれか)
            $event = yield listen(PlayerChatEvent::class)
                ->filter(function($ev){ return
                    $ev->getMessage() === "#1" or
                    $ev->getMessage() === "#2" or
                    $ev->getMessage() === "#3" or
                    $ev->getMessage() === "#4" or
                    $ev->getMessage() === "#5";
                });

            //そうだったらメッセージ送信の取り消し
            $event->setCancelled();

            //各種該当メッセージの処理
            if ($event->getMessage() === "#1") {
                $player->sendMessage(date("Y年n月d日 H時i分")." です\n\n");
            }
            if ($event->getMessage() === "#2") {
                $players = Server::getInstance()->getOnlinePlayers();
                $player->sendMessage(count($players)."人です\n\n");
            }
            if ($event->getMessage() === "#3") {
                $players = Server::getInstance()->getOnlinePlayers();
                foreach ($players as $p) {
                    $player->sendMessage($p->getName()."\n");
                }
                $player->sendMessage("\n");
            }
            if ($event->getMessage() === "#4") {
                $motd = Server::getInstance()->getMotd();
                $port = 19132;
                $ip = "123.456.78.90";
                $player->sendMessage($motd."\n".$ip." : ".$port."\n\n");
            }
            if ($event->getMessage() === "#5") {
                $player->sendMessage("さようなら");
                break;
            }

            //遅延
            yield delay (3*20);
            $player->sendMessage("他に何か知りたいことはありますか？\n  §7> 日時: #1\n  > 人数: #2\n  > 参加者: #3\n  > 鯖: #4\n  > ない: #5\n");
        }

        //再び待機させる
        $this->start($this->chatToTapUser());
    }
}