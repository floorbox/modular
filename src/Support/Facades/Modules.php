<?php

namespace InterNACHI\Modular\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;

/**
 * @method static ModuleConfig|null module(string $name)
 * @method static ModuleConfig|null moduleForPath(string $path)
 * @method static ModuleConfig|null moduleForClass(string $fqcn)
 * @method static Collection modules()
 * @method static Collection reload()
 *
 * @see \InterNACHI\Modular\Support\ModuleRegistry
 */
class Modules extends Facade
{
	/**
	 * @see \InterNACHI\Modular\Support\ModuleRegistry
	 */
	protected static function getFacadeAccessor(): string
	{
		return 'modules';
	}
}
