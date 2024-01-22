<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\tools\generate_entity_ids;

if(count($argv) !== 2){
	fwrite(STDERR, "Required arguments: path to level sound event ID mapping file\n");
	exit(1);
}

$jsonRaw = file_get_contents($argv[1]);
if($jsonRaw === false){
	fwrite(STDERR, "Failed to read level sound event ID mapping file\n");
	exit(1);
}

$list = json_decode($jsonRaw, true, flags: JSON_THROW_ON_ERROR);
if(!is_array($list)){
	fwrite(STDERR, "Failed to decode level sound event ID mapping file, expected a JSON object\n");
	exit(1);
}
$list = array_flip($list);

//TODO: these should probably be patched into the mapping mod
const MISSING = [
	151 => "imitate.endermite",
	195 => "lt.reaction.glow_stick",
	196 => "lt.reaction.glow_stick_2",
	197 => "lt.reaction.luminol",
	198 => "lt.reaction.salt",
	222 => "spawn.baby",
];
const ALIASES = [
	"POWER_ON_SCULK_SENSOR" => [
		"SCULK_SENSOR_POWER_ON"
	],
	"POWER_OFF_SCULK_SENSOR" => [
		"SCULK_SENSOR_POWER_OFF"
	],
	"CAULDRON_DRIP_WATER_POINTED_DRIPSTONE" => [
		"POINTED_DRIPSTONE_CAULDRON_DRIP_WATER"
	],
	"CAULDRON_DRIP_LAVA_POINTED_DRIPSTONE" => [
		"POINTED_DRIPSTONE_CAULDRON_DRIP_LAVA"
	],
	"DRIP_WATER_POINTED_DRIPSTONE" => [
		"POINTED_DRIPSTONE_DRIP_WATER"
	],
	"DRIP_LAVA_POINTED_DRIPSTONE" => [
		"POINTED_DRIPSTONE_DRIP_LAVA"
	],
	"PICK_BERRIES_CAVE_VINES" => [
		"CAVE_VINES_PICK_BERRIES"
	],
	"TILT_DOWN_BIG_DRIPLEAF" => [
		"BIG_DRIPLEAF_TILT_DOWN"
	],
	"TILT_UP_BIG_DRIPLEAF" => [
		"BIG_DRIPLEAF_TILT_UP"
	],
	"MOB_PLAYER_HURT_DROWN" => [
		"PLAYER_HURT_DROWN"
	],
	"MOB_PLAYER_HURT_ON_FIRE" => [
		"PLAYER_HURT_ON_FIRE"
	],
	"MOB_PLAYER_HURT_FREEZE" => [
		"PLAYER_HURT_FREEZE"
	],
	"ITEM_SPYGLASS_USE" => [
		"USE_SPYGLASS"
	],
	"ITEM_SPYGLASS_STOP_USING" => [
		"STOP_USING_SPYGLASS"
	],
	"CHIME_AMETHYST_BLOCK" => [
		"AMETHYST_BLOCK_CHIME"
	],
	"BLOCK_SCULK_CATALYST_BLOOM" => [
		"SCULK_CATALYST_BLOOM"
	],
	"BLOCK_SCULK_SHRIEKER_SHRIEK" => [
		"SCULK_SHRIEKER_SHRIEK"
	],
	"NEARBY_CLOSE" => [
		"WARDEN_NEARBY_CLOSE"
	],
	"NEARBY_CLOSER" => [
		"WARDEN_NEARBY_CLOSER"
	],
	"NEARBY_CLOSEST" => [
		"WARDEN_NEARBY_CLOSEST"
	],
	"AGITATED" => [
		"WARDEN_SLIGHTLY_ANGRY"
	]
];

foreach(MISSING as $id => $name){
	if(!isset($list[$id])){
		$list[$id] = $name;
	}elseif($list[$id] !== $name){
		echo "WARNING: Mismatch of expected name for ID $id: expected $name, got {$list[$id]}\n";
	}
}
ksort($list, SORT_NUMERIC);

$output = fopen(dirname(__DIR__) . "/src/types/LevelSoundEvent.php", "wb");
if($output === false){
	throw new \RuntimeException("Failed to open output file");
}

fwrite($output, <<<'CODE'
<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

/**
 * This file is generated from level_sound_id_map.json in BedrockData.
 *
 * This file is automatically generated; do NOT edit it by hand.
 */
final class LevelSoundEvent{
	private function __construct(){
		//NOOP
	}

CODE);

$prev = 0;
foreach($list as $id => $name){
	$constantName = strtoupper(str_replace(".", "_", $name));
	if($id !== $prev + 1){
		fwrite($output, "\n");
	}
	$prev = $id;
	fwrite($output, "\tpublic const $constantName = $id;\n");
}
fwrite($output, "\n");
fwrite($output, "\t//The following aliases are kept for backwards compatibility only\n");
foreach(ALIASES as $origin => $aliases){
	foreach($aliases as $alias){
		fwrite($output, "\tpublic const $alias = self::$origin;\n");
	}
}

fwrite($output, "}\n");
fclose($output);

echo "Successfully regenerated LevelSoundEvent enum" . PHP_EOL;
