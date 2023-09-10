<?php

declare(strict_types=1);

namespace hcf\command;

use hcf\HCFLoader;
use hcf\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class LeaderboardsCommands extends Command
{
    
    /**
     * LeaderboardsCommand construct.
     */
    public function __construct()
    {
        parent::__construct('leaderboards', 'Use command for leaderboards');
        $this->setPermission("use.player.command");
    }

    /**
     * @return array
     */
    private function getKills(): array
    {
        $kills = [];

        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            $kills[$session->getName()] = $session->getKills();
        }
        return $kills;
    }

    /**
     * @return array
     */
    private function getDeaths(): array
    {
        $deaths = [];

        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            $deaths[$session->getName()] = $session->getDeaths();
        }
        return $deaths;
    }
    
    /**
     * @return array
     */
    private function getKDR(): array
    {
        $kdr = [];

        foreach (HCFLoader::getInstance()->getSessionManager()->getSessions() as $session) {
            if ($session->getDeaths() === 0)
                $kdr[$session->getName()] = 0.0;
            else
                $kdr[$session->getName()] = round($session->getKills() / $session->getDeaths(), 1);
        }
        return $kdr;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUse /leaderboards [kills/kdr/deaths]'));
            return;
        }
        
        if (strtolower($args[0]) === 'kills') {
            if (!isset($args[1])) {
                $data = $this->getKills();
                arsort($data);

                $sender->sendMessage(TextFormat::colorize('&4Leaderboard Kills'));

                for ($i = 0; $i < 10; $i++) {
                    $position = $i + 1;
                    $players = array_keys($data);
                    $kills = array_values($data);

                    if (isset($players[$i]))
                        $sender->sendMessage(TextFormat::colorize('&7#' . $position . '. &f' . $players[$i] . ' &7- &f' . $kills[$i]));
                }
            }
        }
        
        if (strtolower($args[0]) === 'kdr') {
            if (!isset($args[1])) {
                $data = $this->getKDR();
                arsort($data);

                $sender->sendMessage(TextFormat::colorize('&aLeaderboard KDR'));

                for ($i = 0; $i < 10; $i++) {
                    $position = $i + 1;
                    $players = array_keys($data);
                    $kills = array_values($data);

                    if (isset($players[$i]))
                        $sender->sendMessage(TextFormat::colorize('&7#' . $position . '. &f' . $players[$i] . ' &7- &f' . $kills[$i]));
                }
            }
        }
        
        if (strtolower($args[0]) === 'deaths') {
            if (!isset($args[1])) {
                $data = $this->getDeaths();
                arsort($data);

                $sender->sendMessage(TextFormat::colorize('&cLeaderboard Deaths'));

                for ($i = 0; $i < 10; $i++) {
                    $position = $i + 1;
                    $players = array_keys($data);
                    $kills = array_values($data);

                    if (isset($players[$i]))
                        $sender->sendMessage(TextFormat::colorize('&7#' . $position . '. &f' . $players[$i] . ' &7- &f' . $kills[$i]));
                }
            }
        }
    }
}
